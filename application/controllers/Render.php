<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: GET, OPTIONS, POST");

class Render extends CI_Controller {
	public function index()
	{
		$realpath = realpath("./public");
		$config['upload_path']          = $realpath;
        $config['allowed_types']        = 'gif|jpg|png|mp4';
        $config['max_size']             = 100000000;
        $config['max_width']            = 1024;
        $config['max_height']           = 768;

        $this->load->library('upload', $config);

        if ( ! $this->upload->do_upload('userfile'))
        {
        		$error = array('error' => $this->upload->display_errors());
        		echo json_encode($error);
        		return ;
        }
        else
        {
        		$data = array('upload_data' => $this->upload->data());
        }

	$full_path = $data['upload_data']['full_path'];
	$raw_name = $data['upload_data']['raw_name'];
	$name = $raw_name . '.master.m3u8';
	$command = "ffmpeg -i $full_path -preset ultrafast  -map 0:v:0 -map 0:a:0 -map 0:v:0 -map 0:a:0 -c:v h264 -profile:v main -crf 20 -sc_threshold 0 -g 48 -keyint_min 48 -c:a aac -ar 48000 -filter:v:0 scale=\"trunc(oh*a/2)*2:240\" -maxrate:v:0 856k -bufsize:v:0 1200k -b:a:0 96k -filter:v:1 scale=\"trunc(oh*a/2)*2:480\" -maxrate:v:1 1498k -bufsize:v:1 2100k -b:a:1 128k -var_stream_map \"v:0,a:0,name:240p v:1,a:1,name:480p\" -hls_time 4 -hls_list_size 0 -master_pl_name $name -hls_segment_filename ./public/%v_%03d.ts ./public/%v.m3u8 2>&1";
	exec($command);
	header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['data'=>$name]);
	}
}
