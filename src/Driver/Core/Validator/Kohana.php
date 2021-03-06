<?php
/**
 * @license MIT
 * Full license text in LICENSE file
 */

namespace
{
    trait Driver_Core_Validator_Kohana
    {
        protected $instance;
        private $key;
        private $mimetypes;
        private $filesize;

        public function setup(array $input_data)
        {
            $this->instance = Validation::factory($input_data);
        }

        public function add_required_rule($key)
        {
            $this->rule($key, 'not_empty');
        }

        public function add_credit_card_rule($key)
        {
            $this->rule($key, 'credit_card');
        }

        public function add_email_rule($key)
        {
            $this->rule($key, 'email');
        }

        public function add_email_domain_rule($key)
        {
            $this->rule($key, 'email_domain');
        }

        public function add_min_length_rule($key, $number_of_chars)
        {
            $this->rule($key, 'min_length', $number_of_chars);
        }

        public function add_max_length_rule($key, $number_of_chars)
        {
            $this->rule($key, 'max_length', $number_of_chars);
        }

        public function add_file_exists_rule($key)
        {
            $this->key = $key;
            $this->rule($key, 'not_empty');
            $this->add_callback($key, array($this, 'does_file_exist'), array($key));
        }

        public function does_file_exist($file_path)
        {
            if (is_file($file_path))
                return TRUE;
            elseif (isset($_FILES[$this->key]))
                return Upload::not_empty($_FILES[$this->key]);
            else
                return FALSE;
        }

        public function add_file_type_rule($key, array $mimetypes)
        {
            $this->key = $key;
            $this->mimetypes = $mimetypes;
            $this->add_callback($key, array($this, 'is_file_valid_mimetype'), array($this->key));
        }

        public function is_file_valid_mimetype($file_path)
        {
            $has_files_global = FALSE;
            if (isset($_FILES[$this->key]) AND ! empty($_FILES[$this->key]['tmp_name']))
            {
                $file_path = $_FILES[$this->key]['tmp_name'];
                $has_files_global = TRUE;
            }

            $extensions = array();
            foreach ($this->mimetypes as $mimetype)
            {
                $valid_exts = File::exts_by_mime($mimetype);
                foreach ($valid_exts as $ext)
                {
                    $extensions[] = $ext;
                }
            }

            $mimetype = explode(';', finfo_file(finfo_open(FILEINFO_MIME), $file_path))[0];

            if ($has_files_global)
                return in_array($mimetype, $this->mimetypes)
                    AND Upload::type($_FILES[$this->key], $extensions);
            else
                return in_array($mimetype, $this->mimetypes);
        }

        public function add_max_file_size_rule($key, $bytes)
        {
            $this->key = $key;
            $this->filesize = $bytes;
            $this->add_callback($key, array($this, 'is_smaller_than_filesize'), array($this->key));
        }

        public function is_smaller_than_filesize($file_path)
        {
            if (isset($_FILES[$this->key]))
                return Upload::size($_FILES[$this->key], $this->filesize.'B');
            else
                return filesize($file_path);
        }

        public function add_callback($key, array $function, array $arguments = array())
        {
            $data = $this->instance->data();
            foreach ($arguments as $arg_key => $arg)
            {
                $arguments[$arg_key] = $data[$arg];
            }
            $this->instance->rule($key, $function, $arguments);
        }

        public function is_valid()
        {
            return $this->instance->check();
        }

        public function get_error_keys()
        {
            $errors = array();
            foreach ($this->instance->errors() as $field => $message)
            {
                $errors[] = $field;
            }
            return $errors;
        }

        protected function rule($key, $rule, $arg = NULL)
        {
            switch ($rule) {
                case 'upload_valid':
                    $this->instance->rule($key, 'Upload::valid');
                    break;
                case 'upload_not_empty':
                    $this->instance->rule($key, 'Upload::not_empty');
                    break;
                case 'upload_type':
                    $this->instance->rule($key, 'Upload::type', array(':value', $arg));
                    break;
                case 'upload_size':
                    $this->instance->rule($key, 'Upload::size', array(':value', $arg));
                    break;
                default:
                    if ($arg !== NULL)
                    {
                        $this->instance->rule($key, $rule, array(':value', $arg));
                    }
                    else
                    {
                        $this->instance->rule($key, $rule);
                    }
                    break;
            }
        }
    }
}

namespace Driver\Core\Validator
{
    class Kohana implements \Driver\Core\Tool\Validator
    {
        use \Driver_Core_Validator_Kohana;
    }
}
