<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Services\CertLayout\A4Layout;
use App\Services\CertLayout\CertLayoutInterface;
use App\Services\CertLayout\Screen16x9Layout;
use PhpOffice\PhpPresentation\IOFactory;
use PhpOffice\PhpPresentation\PhpPresentation;
use PhpOffice\PhpPresentation\Style\Color;
use PhpOffice\PhpPresentation\DocumentLayout;
use PhpOffice\PhpPresentation\Style\Fill;
use PhpOffice\PhpPresentation\Shape\Drawing;
use PhpOffice\PhpPresentation\Style\Border;


class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'orderint',
        'winner',
        'awardname',
        'year',
        'eventname',
        'creator',
        'company',
        'date',
        'content',
        'presenter',
        'template'
    ];
    //
    /**
     * OrderInt をstep ずつで再設定する
     */
    public static function reorderint(int $step = 10): void
    {
        $all = Certificate::orderBy('orderint')->get();
        $num = $step;
        foreach ($all as $cert) {
            $cert->orderint = $num;
            $cert->save();
            $num += $step;
        }
    }
    public static function exportPPTX($certificates, array $boothData, bool $forPrint = false)
    {
        //新規プレゼンテーション作成
        $phpPres = new PhpPresentation();
        //ドキュメントのプロパティ設定
        $phpPres->getDocumentProperties()
            ->setCreator($certificates[0]->creator ?? '情報処理学会')
            ->setCompany($certificates[0]->company ?? '情報処理学会')
            ->setTitle(($certificates[0]->eventname ?? '情報教育シンポジウム') . "表彰状")
            ->setDescription($certificates[0]->awardname ?? '優秀論文賞');

        /** @var CertLayoutInterface $layout */
        if ($forPrint) {
            $phpPres->getLayout()->setDocumentLayout(DocumentLayout::LAYOUT_A4, false); //false=portrait
            $layout = new A4Layout();

            $oLayout = $phpPres->getLayout();
            $width = $oLayout->getCX($oLayout::UNIT_PIXEL);
            $height = $oLayout->getCY($oLayout::UNIT_PIXEL);
        } else {
            $phpPres->getLayout()->setDocumentLayout(DocumentLayout::LAYOUT_SCREEN_16X9, true);
            $layout = new Screen16x9Layout();

            //マスタースライドに画像を設定
            $oMasterSlide = $phpPres->getAllMasterSlides()[0];

            $oLayout = $phpPres->getLayout();
            $width = $oLayout->getCX($oLayout::UNIT_PIXEL);
            $height = $oLayout->getCY($oLayout::UNIT_PIXEL);

            $a4width = floor($height / 1.41);
            $img = new Drawing\File();
            $hamidasi = 16; // X方向の画像はみだし量(pixel) Y方向のはみだし量は、これの1.4倍になる
            $img->setName('Image File')
                ->setDescription('Image File')
                ->setPath(storage_path('app/template1.png'))
                ->setHeight($height + round($hamidasi * 2 * 1.4))
                ->setOffsetX(($width - $a4width) / 2 - $hamidasi)
                ->setOffsetY(round(-$hamidasi * 1.4));
            $img->getBorder()->setColor(new Color('FFffffff'))->setDashStyle(Border::DASH_SOLID)->setLineStyle(Border::LINE_SINGLE);
            $oMasterSlide->addShape($img);
            // 紙のまわりの枠線（画像の縁よりも、すこし内側に表示する）
            $border = $oMasterSlide->createRichTextShape()
                ->setHeight($height)
                ->setWidth($a4width)
                ->setOffsetX(($width - $a4width) / 2)
                ->setOffsetY(0);
            $border->getFill()->setFillType(Fill::FILL_NONE);
            $border->getBorder()->setColor(new Color('FF333333'))->setDashStyle(Border::DASH_SOLID)->setLineStyle(Border::LINE_SINGLE);

            // $img の左ボーダーが消えないので、白い矩形を置く
            $kesi = $oMasterSlide->createRichTextShape()
                ->setHeight($height + $hamidasi * 2)
                ->setWidth(20)
                ->setOffsetX(($width - $a4width) / 2 - $hamidasi - 10)
                ->setOffsetY(-$hamidasi);
            $kesi->getFill()->setFillType(Fill::FILL_SOLID)->setRotation(90)->setStartColor(new Color('FFffffff'))->setEndColor(new Color('FFffffff'));
        }

        // 差し込み

        $obj = $boothData;

        foreach ($certificates as $cert) {
            $cert->fillTemplate();
            $baseary["awardname"] = $cert->awardname; // 〜〜賞
            $baseary["content"] = $cert->content;
            $baseary["year"] = $cert->year; // 2026
            $baseary["eventname"] = $cert->eventname; // 情報教育
            $baseary["date"] = $cert->date; // 令和８年８月２１日
            $baseary["creator"] = $cert->creator; // @IPSJ
            $baseary["company"] = $cert->company; // 情報処理学会
            $baseary["presenter"] = $cert->presenter; // [:date:]\r\n一般社団法人 [:company:]\r\n[:eventname:]\r\nプログラム委員長XX XX\r\n実行委員長YY YY\r\n大会委員長ZZ ZZ
            $baseary["template"] = $cert->template;
            // $baseary["title"] = "{$baseary['eventname']} {$baseary['awardname']} 表彰状";


            $booth_list = $cert->winner; // 受賞者のブース番号
            if (strlen($booth_list) > 1) {
                $booth_list_ary = explode(" ", trim($booth_list));
                foreach ($booth_list_ary as $b) {
                    $ary = $obj[$b];
                    $ary = array_merge($ary, $baseary);
                    $layout->addSlide($phpPres, $ary);
                }
            } else {
                // 全部出力する場合
                foreach ($obj as $booth => $ary) {
                    $ary = array_merge($ary, $baseary);
                    $layout->addSlide($phpPres, $ary);
                }
            }
        }

        $phpPres->removeSlideByIndex(0); // 最初のスライドを削除する


        $oWriterPPTX = IOFactory::createWriter($phpPres, 'PowerPoint2007');
        $tempFile = tempnam(sys_get_temp_dir(), 'pptx_');
        $oWriterPPTX->save($tempFile);
        self::fixCjkFonts($tempFile);
        $content = file_get_contents($tempFile);
        unlink($tempFile);
        return $content;
    }

    /** スライドXML内の <a:ea typeface="X"/> の前に <a:latin typeface="X"/> を挿入し、dirty="0" でキャッシュ使用を指示 */
    private static function fixCjkFonts(string $pptxPath): void
    {
        $zip = new \ZipArchive();
        if ($zip->open($pptxPath) !== true) return;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (!preg_match('#^ppt/slides/slide\d+\.xml$#', $name)) continue;

            $xml = $zip->getFromIndex($i);
            // charset="-128"(SHIFTJIS=0x80)で日本語フォントとして識別させ、pitchFamily="18"でセリフ可変幅と明示
            $xml = preg_replace(
                '/<a:ea typeface="([^"]+)"[^>]*\/>/',
                '<a:latin typeface="$1" pitchFamily="18" charset="-128"/><a:ea typeface="$1" pitchFamily="18" charset="-128"/>',
                $xml
            );
            // dirty="0": PowerPoint に再計算させず指定フォントをそのまま使用させる
            $xml = preg_replace('/<a:rPr /', '<a:rPr dirty="0" ', $xml);
            $zip->addFromString($name, $xml);
        }
        $zip->close();
    }

    public function fillTemplate(): void
    {
        $replace_targets = ['eventname', 'content', 'presenter'];
        $embed_items = [ 'year', 'company', 'eventname', 'awardname', 'date'];
        foreach($replace_targets as $target) {
            foreach($embed_items as $item) {
                $this->$target = str_replace("[:".strtolower($item).":]", $this->$item, $this->$target);
            }
        }
    }
}
