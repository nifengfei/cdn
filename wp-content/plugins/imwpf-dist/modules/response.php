<?php
 namespace imwpf\modules; class response { public static function success($data) { $result = array( 'errno' => 0, 'data' => $data ); echo json_encode($result); } public function failure($msg, $errno = 1) { $result = array( 'errno' => $errno, 'msg' => $msg ); echo json_encode($result); } } 