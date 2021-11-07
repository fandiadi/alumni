<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Import extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->form_validation->set_error_delimiters($this->config->item('error_start_delimiter', 'ion_auth'), $this->config->item('error_end_delimiter', 'ion_auth'));
        $this->load->model('M_Import');
    }


    public function index()
    {

        $data['users'] = $this->M_Import->view();
        $this->load->view('admin/import', $data);
        $this->template->_render_page('layouts/backend', $this->data);
    }

    public function import()
    {
        $this->load->view('admin/import');
    }


    public function upload()
    {
        if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin()) {
            redirect('auth', 'refresh');
        }

        // Load plugin PHPExcel nya
        include APPPATH . 'third_party/PHPExcel/PHPExcel.php';

        $config['upload_path'] = realpath('excel');
        $config['allowed_types'] = 'xlsx|xls|csv';
        $config['max_size'] = '10000';
        $config['encrypt_name'] = true;

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload()) {

            //upload gagal
            $this->session->set_flashdata('notif', '<div class="alert alert-danger"><b>PROSES IMPORT GAGAL!</b> ' . $this->upload->display_errors() . '</div>');
            //redirect halaman
            echo (error_log);

            redirect('import/import');
        } else {

            $data_upload = $this->upload->data();

            $excelreader     = new PHPExcel_Reader_Excel2007();
            $loadexcel       = $excelreader->load('excel/' . $data_upload['file_name']); // Load file yang telah diupload ke folder excel
            $sheet           = $loadexcel->getActiveSheet()->toArray(null, true, true, true, true);

            $data = array();

            $numrow = 1;
            foreach ($sheet as $row) {
                if ($numrow > 1) {
                    array_push($data, array(
                        'username'                => $row['A'],
                        'password'               => '12345',
                        'nim'      => $row['A'],
                        'first_name'             => $row['B'],
                        'email'             => $row['C'],
                    ));
                }
                $numrow++;
            }
            $this->db->insert_batch('users', $data);
            //delete file from server
            unlink(realpath('excel/' . $data_upload['file_name']));

            //upload success
            $this->session->set_flashdata('notif', '<div class="alert alert-success"><b>PROSES IMPORT BERHASIL!</b> Data berhasil diimport!</div>');
            //redirect halaman
            redirect('import');
        }
    }
}
