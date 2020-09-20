<?php
namespace imwpf\modules; use imwpf\admin\Options; class WeiXin { public function setToken($token) { $this->token = $token; return $this; } public function checkSignature($signature, $timestamp, $nonce) { $tmpArr = array($this->token, $timestamp, $nonce); sort($tmpArr, SORT_STRING); if (sha1(implode($tmpArr)) == $signature) { return true; } else { return false; } } public function sendMessage($to, $from, $content) { $xml = "<xml>
  <ToUserName><![CDATA[%s]]></ToUserName>
  <FromUserName><![CDATA[%s]]></FromUserName>
  <CreateTime>%d</CreateTime>
  <MsgType><![CDATA[text]]></MsgType>
  <Content><![CDATA[%s]]></Content>
  <MsgId>%d</MsgId>
</xml>"; $result = sprintf($xml, $to, $from, time(), $content, time()); return $result; } public function getResponse($msg) { $content = apply_filters('imwpf_weixin_response', $msg); if ($content == $msg) { $opt = Options::getInstance('imwpf_options')->get('weixin'); return $opt['help']; } return $content; } }