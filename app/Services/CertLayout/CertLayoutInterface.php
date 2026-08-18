<?php

namespace App\Services\CertLayout;

use PhpOffice\PhpPresentation\PhpPresentation;
use PhpOffice\PhpPresentation\Slide;

interface CertLayoutInterface
{
    public function addSlide(PhpPresentation $pres, array $ary): Slide;
}
