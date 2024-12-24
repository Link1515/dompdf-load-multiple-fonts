# Dompdf Load Multiple Fonts

I modified [this script](https://github.com/dompdf/utils/blob/master/load_font.php), originally provided by [dompdf](https://github.com/dompdf). The updated script I created is designed to load multiple fonts into the dompdf extension.

## How to use

### Modify load_fonts.php

1. Point to the composer or dompdf autoloader

```PHP
require_once 'vendor/autoload.php';
```

2. [Optional] Set the path to your font directory

By default dompdf loads fonts to dompdf/lib/fonts. If you have modified your font directory set this variable appropriately.

```PHP
$fontDir = 'storage/fonts';
```

3. configure your fonts

`normal` font is required, and `bold`, `italic`, and `bold_italic` are optional

format:

```PHP
$fonts = [
    'font_name' => [
        'normal'      => 'font_path (the font file you want to load)', // required
        'bold'        => 'font_path (the font file you want to load)', // optional
        'italic'      => 'font_path (the font file you want to load)', // optional
        'bold_italic' => 'font_path (the font file you want to load)', // optional
    ]
];
```

Example:

```PHP
$fonts = [
    'msjh' => [
        'normal'      => './Microsoft JhengHei Regular.ttf',
        'bold'        => './Microsoft JhengHei Bold.ttf',
        'italic'      => './Microsoft JhengHei Italic.ttf',
        'bold_italic' => './Microsoft JhengHei Bold Italic.ttf'
    ],
    'notosanstc' => [
        'normal'      => './NotoSansTC-Regular.ttf',
        'bold'        => './NotoSansTC-Bold.ttf',
        'italic'      => './NotoSansTC-Italic.ttf',
        'bold_italic' => './NotoSansTC-Bold-Italic.ttf'
    ]
];
```

### run command:

```Bash
php load_fonts.php
```
