<?php
/**
 * @license MIT
 * Full license text in LICENSE file
 */

namespace Driver\Core\Tool;

interface Validator
{
    public function setup(array $input_data);

    public function add_required_rule($key);

    public function add_email_rule($key);

    public function add_email_domain_rule($key);

    public function add_min_length_rule($key, $number_of_chars);

    public function add_max_length_rule($key, $number_of_chars);

    public function add_file_exists_rule($key);

    public function add_file_type_rule($key, array $mimetypes);

    public function add_max_file_size_rule($key, $bytes);

    public function add_callback($key, array $function, array $arguments);

    public function is_valid();

    public function get_error_keys();
}
