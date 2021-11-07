<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class M_Import extends CI_Model
{
    public function view()
    {
        return $this->db->get('users')->result();
    }
}
