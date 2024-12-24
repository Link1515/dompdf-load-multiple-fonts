<?php

// This script is modifed from https://github.com/dompdf/utils/blob/master/load_font.php

// 1. [Required] Point to the composer or dompdf autoloader
require_once 'vendor/autoload.php';

// 2. [Optional] Set the path to your font directory
//    By default dompdf loads fonts to dompdf/lib/fonts
//    If you have modified your font directory set this
//    variable appropriately.
$fontDir = 'storage/fonts';

// 3. configure your fonts
// `normal` font is required, and `bold`, `italic`, and `bold` italic are optional
// format:
// $fonts = [
//     'font_name' => [
//         'normal'      => 'font_path (the font file you want to load)', // required
//         'bold'        => 'font_path (the font file you want to load)', // optional
//         'italic'      => 'font_path (the font file you want to load)', // optional
//         'bold_italic' => 'font_path (the font file you want to load)', // optional
//     ]
// ];
//
// Example:
// $fonts = [
//     'msjh' => [
//         'normal'      => './Microsoft JhengHei Regular.ttf',
//         'bold'        => './Microsoft JhengHei Bold.ttf',
//         'italic'      => './Microsoft JhengHei Italic.ttf',
//         'bold_italic' => './Microsoft JhengHei Bold Italic.ttf'
//     ],
//     'notosanstc' => [
//         'normal'      => './NotoSansTC-Regular.ttf',
//         'bold'        => './NotoSansTC-Bold.ttf',
//         'italic'      => './NotoSansTC-Italic.ttf',
//         'bold_italic' => './NotoSansTC-Bold-Italic.ttf'
//     ]
// ];
$fonts = [
    'your font' => [
        'normal' => '',
        'bold'   => '',
    ]
];

// 4. run command:
//    php load_fonts.php

// *** DO NOT MODIFY BELOW THIS POINT ***

use Dompdf\Dompdf;
use Dompdf\Exception;
use FontLib\Font;

$dompdf = new Dompdf();
if (isset($fontDir) && realpath($fontDir) !== false) {
    $dompdf->getOptions()->set('fontDir', $fontDir);
}

/**
 * Installs new font family
 *
 * @param Dompdf $dompdf      dompdf main object
 * @param array $fonts        fonts array
 *
 * @throws Exception
 */
function install_font_family($dompdf, $fonts)
{
    if (empty($fonts)) {
        throw new \Exception('No fonts is passed. Please check $fonts variable in load_fonts.php.');
    }

    $fontMetrics = $dompdf->getFontMetrics();

    foreach ($fonts as $fontname => $font) {
        $normal      = $font['normal'];
        $bold        = isset($font['bold']) ? $font['bold'] : null;
        $italic      = isset($font['italic']) ? $font['italic'] : null;
        $bold_italic = isset($font['bold_italic']) ? $font['bold_italic'] : null;

        // Check if the base filename is readable
        if (!is_readable($normal)) {
            throw new Exception("Unable to read '$normal'.");
        }

        $dir      = dirname($normal);
        $basename = basename($normal);
        $last_dot = strrpos($basename, '.');
        if ($last_dot !== false) {
            $file = substr($basename, 0, $last_dot);
            $ext  = strtolower(substr($basename, $last_dot));
        } else {
            $file = $basename;
            $ext  = '';
        }

        if (!in_array($ext, ['.ttf', '.otf'])) {
            throw new Exception("Unable to process fonts of type '$ext'.");
        }

        // Try $file_Bold.$ext etc.
        $path = "$dir/$file";

        $patterns = [
            'bold'        => ['_Bold', 'b', 'B', 'bd', 'BD'],
            'italic'      => ['_Italic', 'i', 'I'],
            'bold_italic' => ['_Bold_Italic', 'bi', 'BI', 'ib', 'IB'],
        ];

        foreach ($patterns as $type => $_patterns) {
            if (!isset($$type) || !is_readable($$type)) {
                foreach ($_patterns as $_pattern) {
                    if (is_readable("$path$_pattern$ext")) {
                        $$type = "$path$_pattern$ext";
                        break;
                    }
                }

                if (is_null($$type)) {
                    echo("Unable to find $type face file.\n");
                }
            }
        }

        $fonts = compact('normal', 'bold', 'italic', 'bold_italic');
        $entry = [];

        // Copy the files to the font directory.
        foreach ($fonts as $var => $src) {
            if (is_null($src)) {
                $entry[$var] = $dompdf->getOptions()->get('fontDir') . '/' . mb_substr(basename($normal), 0, -4);
                continue;
            }

            // Verify that the fonts exist and are readable
            if (!is_readable($src)) {
                throw new Exception("Requested font '$src' is not readable");
            }

            $dest = $dompdf->getOptions()->get('fontDir') . '/' . basename($src);

            if (!is_writeable(dirname($dest))) {
                throw new Exception("Unable to write to destination '$dest'.");
            }

            echo "Copying $src to $dest...\n";

            if (!copy($src, $dest)) {
                throw new Exception("Unable to copy '$src' to '$dest'");
            }

            $entry_name = mb_substr($dest, 0, -4);

            echo "Generating Adobe Font Metrics for $entry_name...\n";

            $font_obj = Font::load($dest);
            $font_obj->saveAdobeFontMetrics("$entry_name.ufm");
            $font_obj->close();

            $entry[$var] = $entry_name;
        }

        // Store the fonts in the lookup table
        $fontMetrics->setFontFamily($fontname, $entry);
    }

    // Save the changes
    $fontMetrics->saveFontFamilies();
}

install_font_family($dompdf, $fonts);
