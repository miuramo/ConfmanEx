<?php

namespace App\Services\CertLayout;

use PhpOffice\PhpPresentation\PhpPresentation;
use PhpOffice\PhpPresentation\Slide;
use PhpOffice\PhpPresentation\Style\Alignment;
use PhpOffice\PhpPresentation\Style\Color;
use PhpOffice\PhpPresentation\Style\Font;

class A4Layout implements CertLayoutInterface
{
    public function addSlide(PhpPresentation $pres, array $ary): Slide
    {
        $pres->createSlide();
        $pres->setActiveSlideIndex($pres->getSlideCount() - 1);
        $slide = $pres->getActiveSlide();

        $w600 = 600;
        $this->addRTShape($pres, $slide, $w600, 15, $ary["eventname"], 23);
        $this->addRTShape($pres, $slide, $w600, 20, $ary["awardname"], 26);

        $authors = "";
        $aindex = 0;
        if (count($ary['authors']) % 2 == 1) {
            $authors .= $ary['authors'][0] . "殿\r\n";
            $aindex = 1;
        }
        for ($i = $aindex; $i < count($ary['authors']); $i += 2) {
            $authors .= $ary['authors'][$i] . "殿 　";
            $authors .= $ary['authors'][$i + 1] . "殿";
            $authors .= "\r\n";
        }
        $additionalline = 0;
        if (count($ary["authors"]) > 2) {
            $additionalline = floor((count($ary["authors"]) - 1) / 2);
        }
        $this->addRTShape($pres, $slide, $w600, 18.5 + $additionalline * 1.2, $authors, 24, Alignment::HORIZONTAL_CENTER, Alignment::VERTICAL_CENTER);

        $ary["content"] = str_replace("[:title:]", $ary["title"], $ary["content"]);
        $this->addRTShape($pres, $slide, 576, 36.5 + $additionalline * 2, $ary["content"], 18, Alignment::HORIZONTAL_GENERAL);

        $title_additionalline = mb_strlen($ary['title']) / 20;
        $this->addRTShape(
            $pres,
            $slide,
            520,
            67 + $additionalline + $title_additionalline * 1.7,
            $ary['presenter'],
            18,
            Alignment::HORIZONTAL_RIGHT
        );

        return $slide;
    }

    private function addRTShape(
        PhpPresentation $pres,
        Slide $slide,
        int $intw,
        float $ypercent,
        string $mes,
        int $size,
        string $h_alignment = Alignment::HORIZONTAL_CENTER,
        string $v_alignment = Alignment::VERTICAL_AUTO
    ): void {
        $oLayout = $pres->getLayout();
        $inth = 300;
        $intoffx = (int)(($oLayout->getCX($oLayout::UNIT_PIXEL) - $intw) / 2);
        $intoffy = (int)($oLayout->getCY($oLayout::UNIT_PIXEL) * $ypercent / 100);

        $shape2 = $slide->createRichTextShape()
            ->setWidth($intw)
            ->setHeight($inth)
            ->setOffsetX($intoffx)
            ->setOffsetY($intoffy);
        $shape2->getActiveParagraph()
            ->getAlignment()
            ->setHorizontal($h_alignment)
            ->setVertical($v_alignment);

        $textRun2 = $shape2->createTextRun($mes);
        $textRun2->getFont()
            ->setName('Hiragino Mincho ProN W3') // TODO: Windowsの場合は変更が必要かも
            ->setFormat(Font::FORMAT_EAST_ASIAN)
            ->setBold(false)
            ->setSize($size)
            ->setColor(new Color('FF000000'));
        $shape2->getActiveParagraph()->setLineSpacing(120);
    }
}
