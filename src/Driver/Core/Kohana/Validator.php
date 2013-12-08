<?php
/**
 * @license MIT
 * Full license text in LICENSE file
 */

namespace
{
    trait Driver_Core_Kohana_Validator
    {
        protected $instance;

        public function setup(array $input_data)
        {
            $this->instance = Validation::factory($input_data);
        }

        public function rule($key, $rule, $arg = NULL)
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

        public function callback($key, array $function, array $args = array())
        {
            $data = $this->instance->data();
            foreach ($args as $arg_key => $arg)
            {
                $args[$arg_key] = $data[$arg];
            }
            array_unshift($args, ':value');
            $this->instance->rule($key, $function, $args);
        }

        public function check()
        {
            return $this->instance->check();
        }

        public function errors()
        {
            $errors = array();
            foreach ($this->instance->errors() as $field => $message)
            {
                $errors[] = $field;
            }
            return $errors;
        }
    }
}

namespace Driver\Core\Kohana
{
    class Validator implements \Driver\Core\Tool\Validator
    {
        use \Driver_Core_Kohana_Validator;
    }
}
