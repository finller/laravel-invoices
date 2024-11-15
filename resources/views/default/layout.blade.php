<!DOCTYPE html>
<html lang="en">

<head>
    <title>{{ $invoice->name }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    @include('invoices::default.style')
</head>

<body>

    @include('invoices::default.invoice')

    <script type="text/php">
            if (isset($pdf)) {
                $text = "Page {PAGE_NUM} / {PAGE_COUNT}";
                $size = 10;
                $defaultFont = $fontMetrics->getOptions()->getDefaultFont();
                $font = $fontMetrics->getFont($defaultFont);
                $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
                $x = ($pdf->get_width() - $width);
                $y = $pdf->get_height() - 35;
                $pdf->page_text($x, $y, $text, $font, $size);
            }
    </script>
</body>

</html>
