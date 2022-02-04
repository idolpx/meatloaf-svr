/**
 * Koala Painter image converter
 *
 * The Commodore 64 version of Koala Painter used a fairly simple file format
 * corresponding directly to the way bitmapped graphics are handled on the
 * computer: A two-byte load address, followed immediately by 8000 bytes of raw
 * bitmap data, 1000 bytes of raw "Video Matrix" data, 1000 bytes of raw "Color
 * RAM" data, and a one-byte Background Color field.
 *
 *   (Source: http://en.wikipedia.org/wiki/KoalaPad#File_Format)
 *
 * This class is based on the koalatoppm application written by Peter Karlsson.
 * <http://git.debian.org/?p=users/peterk/koalatoppm.git>
 *
 * @version 2010-Dec-11
 *
 * @author Marc Ermshaus <http://www.ermshaus.org/>
 * @license GNU General Public License <http://www.gnu.org/licenses/gpl.html>
 */
class KoalaConverter
{
    protected static $pixelmask = array(0xC0, 0x30, 0x0C, 0x03);

    protected static $pixeldisplacement = array(6, 4, 2, 0);

    /**
     * @var array Mappings: C64 colour index -> RGB
     */
    protected static $c64colours = array(
        array(  0,   0,   0), // Black
        array(255, 255, 255), // White
        array(189,  24,  33), // Red
        array( 49, 231, 198), // Cyan
        array(181,  24, 231), // Purple
        array( 24, 214,  24), // Green
        array( 33,  24, 173), // Blue
        array(222, 247,   8), // Yellow
        array(189,  66,   0), // Orange
        array(107,  49,   0), // Brown
        array(255,  74,  82), // Light red
        array( 66,  66,  66), // Gray 1
        array(115, 115, 107), // Gray 2
        array( 90, 255,  90), // Light green
        array( 90,  82, 255), // Light blue
        array(165, 165, 165)  // Gray 3
    );

    /**
     * @var array Stores the image's data as an array of RGB triples
     */
    protected $triples = array();

    /**
     * Returns the RGB value of a colour index
     *
     * @param int $index The colour's index
     * @return array RGB colour triplet
     */
    protected function getColour($index)
    {
        if ($index >= 0 && $index <= 15) {
            return self::$c64colours[$index];
        } else {
            throw new Exception('Unknown colour index: ' . $index);
        }
    }

    /**
     * Loads a Koala Painter (.koa) image
     *
     * @param string $file Path to image file
     */
    public function load($file)
    {
        $tmp = file_get_contents($file);
        $l   = strlen($tmp);
        $ret = array();

        if ($l !== 10003) {
            throw new Exception('Input data of wrong length');
        }

        $loadaddress = substr($tmp,     0,    2);
        $image       = substr($tmp,     2, 8000);
        $colour1     = substr($tmp,  8002, 1000);
        $colour2     = substr($tmp,  9002, 1000);
        $background  = substr($tmp, 10002,    1);

        if ("\x00\x60" !== $loadaddress) {
            throw new Exception('Input data is not a .koa file');
        }

        // Image
        for ($y = 0; $y < 200; $y++) {
            for ($x = 0; $x < 160; $x++) {
                
                // Get value of pixel at (x,y)
                /** @todo not sure how this is supposed to work */
                $index = (int) (floor($x / 4) * 8
                                + ($y % 8)
                                + floor($y / 8) * 320);
                
                $pixel = (ord($image[$index]) & self::$pixelmask[$x % 4])
                         >> self::$pixeldisplacement[$x % 4];

                // Colour index
                /** @todo not sure how this is supposed to work */
                $ci = (int) (floor($x / 4) + floor($y / 8) * 40);
                $k = 0;

                switch ($pixel) {
                    /** @todo I am not sure whether the bitwise operation for
                     *    background is correct (see: C original) */
                    case 0: $k = ord($background) & 0x0F;   break;
                    case 1: $k = ord($colour1[$ci]) >> 4;   break;
                    case 2: $k = ord($colour1[$ci]) & 0x0F; break;
                    case 3: $k = ord($colour2[$ci]) & 0x0F; break;
                    default:
                        throw new Exception('Internal error');
                        break;
                };

                $this->triples[] = $this->getColour($k);
            }
        }
    }

    /**
     * Returns a PNG version of the image
     *
     * @param bool $expand Double every pixel in horizontal direction?
     * @return resource Image resource identifier
     */
    public function exportPng($expand = true)
    {
        $width  = ($expand) ? 320 : 160;
        $height = 200;

        $gd = imagecreatetruecolor($width, $height);

        for ($i = 0; $i < $width * $height; $i++) {
            if ($expand) {
                $rgb = $this->triples[(int) floor($i / 2)];
            } else {
                $rgb = $this->triples[$i];
            }

            $x = $i % $width;
            $y = (int) floor($i / $width);

            $colour = imagecolorallocate($gd, $rgb[0], $rgb[1], $rgb[2]);
            imagesetpixel($gd, $x, $y, $colour);
        }

        return $gd;
    }
}



$koa = new KoalaConverter();
$koa->load('./pic_b_turrican.koa');

header('Content-Type: image/png');

imagepng($koa->exportPng());