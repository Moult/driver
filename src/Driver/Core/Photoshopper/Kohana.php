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
    }
}