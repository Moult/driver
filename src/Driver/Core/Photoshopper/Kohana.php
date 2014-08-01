<?php
/**
 * @license MIT
 * Full license text in LICENSE file
 */

namespace
{
    trait Driver_Core_Photoshopper_Kohana
    {
        public function resize_to_width($width)
        {
            $image = Image::factory($this->source);
            $image->resize($width, NULL);
            $image->save($this->destination, 100);
        }

        public function resize_to_height($height)
        {
            $image = Image::factory($this->source);
            $image->resize(NULL, $height);
            $image->save($this->destination, 100);
        }

        public function square_crop($size)
        {
            $image = Image::factory($this->source);

            list($width, $height) = $this->get_dimensions();

            if ($width > $size OR $height > $size)
                $image->resize($size, $size, Image::PRECISE);

            $width = $image->width;
            $height = $image->height;

            if ($width > $height)
                $image->crop($size, $size, ($width - $size) / 2, 0);
            elseif ($height > $width)
                $image->crop($size, $size, 0, ($height - $size) / 4);

            $image->save($this->destination, 100);
        }

        public function blur($radius = 0, $sigma = 16)
        {
            $image = new Imagick($this->source);
            $image->blurImage($radius, $sigma);
            return $image->writeImage($this->destination);
        }

        public function rotate($degrees)
        {
            $image = Image::factory($this->source);
            $image->rotate($degrees);
            $image->save($this->destination, 100);
        }
    }
}

namespace Driver\Core\Photoshopper
{
    class Kohana
    {
        protected $source;
        protected $destination;

        use \Driver_Core_Photoshopper_Kohana;

        public function setup($source, $destination = NULL)
        {
            $this->source = $source;
            if ($destination === NULL)
            {
                $this->destination = $source;
            }
            else
            {
                $this->destination = $destination;
            }
        }

        public function get_dimensions()
        {
            list($width, $height) = getimagesize($this->source);
            return array($width, $height);
        }

        public function gaussian_blur($sigma)
        {
            shell_exec('convert '.escapeshellarg($this->source).' -filter Gaussian -resize 25% -define filter:sigma='.escapeshellarg($sigma).' -resize 400% '.escapeshellarg($this->destination));
        }

        public function get_geotagged_location_coordinates()
        {
            $mimetype = explode(';', finfo_file(finfo_open(FILEINFO_MIME), $this->source))[0];
            if ($mimetype !== 'image/jpeg'
                AND $mimetype !== 'image/tiff'
                AND $mimetype !== 'image/tiff-fx')
                return NULL;

            $exif = exif_read_data($this->source);

            if ( ! isset($exif['GPSLatitude']))
                return NULL;

            list($lat_deg_numerator, $lat_deg_denominator) = explode('/', $exif['GPSLatitude'][0]);
            $lat_deg = $lat_deg_numerator / $lat_deg_denominator;

            list($lat_min_numerator, $lat_min_denominator) = explode('/', $exif['GPSLatitude'][1]);
            $lat_min = $lat_min_numerator / $lat_min_denominator;

            list($lat_sec_numerator, $lat_sec_denominator) = explode('/', $exif['GPSLatitude'][2]);
            $lat_sec = $lat_sec_numerator / $lat_sec_denominator;

            $latitude = $lat_deg+((($lat_min*60)+($lat_sec))/3600);

            if ($exif['GPSLatitudeRef'] === 'S')
            {
                $latitude = - $latitude;
            }

            list($long_deg_numerator, $long_deg_denominator) = explode('/', $exif['GPSLongitude'][0]);
            $long_deg = $long_deg_numerator / $long_deg_denominator;

            list($long_min_numerator, $long_min_denominator) = explode('/', $exif['GPSLongitude'][1]);
            $long_min = $long_min_numerator / $long_min_denominator;

            list($long_sec_numerator, $long_sec_denominator) = explode('/', $exif['GPSLongitude'][2]);
            $long_sec = $long_sec_numerator / $long_sec_denominator;

            $longitude = $long_deg+((($long_min*60)+($long_sec))/3600);

            if ($exif['GPSLongitudeRef'] === 'W')
            {
                $longitude = - $longitude;
            }

            return array($latitude, $longitude);
        }

        public function get_exif_orientation()
        {
            $mimetype = explode(';', finfo_file(finfo_open(FILEINFO_MIME), $this->source))[0];
            if ($mimetype !== 'image/jpeg'
                AND $mimetype !== 'image/tiff'
                AND $mimetype !== 'image/tiff-fx')
                return NULL;

            $exif = exif_read_data($this->source);
            if (isset($exif['Orientation']))
                return $exif['Orientation'];
            else
                return NULL;
        }

        public function auto_orientate()
        {
            shell_exec('mogrify -auto-orient '.escapeshellarg($this->source));
        }
    }
}
