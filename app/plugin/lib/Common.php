<?php
/**
 * 人人站CMS
 * ============================================================================
 * 版权所有 2015-2030 山东康程信息科技有限公司，并保留所有权利。
 * 网站地址: http://www.rrzcms.com
 * ----------------------------------------------------------------------------
 * 如果商业用途务必到官方购买正版授权, 以免引起不必要的法律纠纷.
 * 严禁反编译、逆向等任何形式的侵权行为，违者将追究法律责任.
 * ============================================================================
 */

namespace app\plugin\lib; use think\Facade; class Common extends Facade { protected static function plugin_get_class($XNIPp) { goto O38N2; Oncnr: $oFWqf = parse_name($XNIPp); goto PE4j_; PE4j_: $fvzcx = "\x61\x64\144\157\x6e\163\134{$oFWqf}\x5c{$XNIPp}\x50\x6c\165\147\151\x6e"; goto Moi69; Moi69: return $fvzcx; goto pWh8v; O38N2: $XNIPp = ucwords($XNIPp); goto Oncnr; pWh8v: } protected static function plugin_get_url($nLI0d, $QHSPl = [], $PLwKp = false) { goto UImu3; s0nU0: $BlH66 = trim($l2wim ? strtolower($nLI0d["\160\141\x74\150"]) : $nLI0d["\160\141\x74\x68"], "\x2f"); goto WoFPU; GUoix: $Ijx31 = $l2wim ? parse_name($nLI0d["\x68\157\x73\164"]) : $nLI0d["\x68\157\x73\x74"]; goto s0nU0; XLiZ3: $YlePl = ["\137\x70\x6c\x75\147\x69\156" => $OIfSA, "\x5f\x63\157\156\164\x72\157\x6c\154\145\162" => $Ijx31, "\x5f\141\143\x74\x69\157\156" => $BlH66]; goto wV18C; D2DqD: $l2wim = true; goto XIMTz; h3HkT: return U($deEci, $QHSPl, true, $PLwKp); goto kogAx; WoFPU: if (!isset($nLI0d["\161\x75\145\x72\171"])) { goto ZnBcW; } goto CkAx3; AxkY4: $QHSPl = array_merge($gaInN, $QHSPl); goto z256a; XIMTz: $OIfSA = $l2wim ? parse_name($nLI0d["\163\143\150\x65\155\145"]) : $nLI0d["\163\143\x68\145\x6d\145"]; goto GUoix; z256a: ZnBcW: goto XLiZ3; UImu3: $nLI0d = parse_url($nLI0d); goto D2DqD; wV18C: $deEci = "\x2f" . getAppName("\x61\144\155\x69\x6e") . "\x2f\160\x6c\x75\147\x69\156\57" . implode("\57", $YlePl); goto h3HkT; CkAx3: parse_str($nLI0d["\161\165\x65\x72\171"], $gaInN); goto AxkY4; kogAx: } protected static function plugin_temp_html($OEcs2 = '', $Rl_Cx = '') { goto g1jH2; g1jH2: $rRd2n = M("\160\154\165\x67\151\x6e")->where("\x73\x74\141\x74\x75\x73\x3d\61\40\141\x6e\x64\x20\151\163\x68\157\x6d\145\x3d\61\x20\141\x6e\144\x20\x69\x73\x6c\157\141\144\x3d\61\x20\x61\x6e\x64\40\50\x6c\157\x61\144\164\x65\155\160\75\47\x27" . ($OEcs2 != '' ? "\x20\157\162\40\x6c\157\x61\144\164\x65\x6d\x70\40\154\x69\153\x65\40\x27\45" . $OEcs2 . "\x2c\x25\x27" : '') . "\51")->order("\x73\157\x72\164")->column("\x63\x6f\x64\x65"); goto iLkPu; cmX2x: return $Rl_Cx; goto FkaS3; KqDXO: j68rM: goto cmX2x; iLkPu: foreach ($rRd2n as $XNIPp) { goto DvnKe; qzw8d: $OIfSA = new $fvzcx(); goto R3piX; R3piX: if (isset($OIfSA->info["\143\x6f\x6e\x66\x69\147"]["\x6c\x6f\x61\x64\x68\164\155\154"]) && $OIfSA->info["\143\x6f\156\x66\x69\x67"]["\154\x6f\141\144\150\164\155\154"] == 1) { goto Hs1sd; } goto xQheY; xQheY: $Rl_Cx = $Rl_Cx . $OIfSA->getHtml(); goto BlXQ3; Y1zC3: Q_lkE: goto vDcsI; DvnKe: $fvzcx = self::plugin_get_class($XNIPp); goto oFPOx; BlXQ3: goto HDs9L; goto RQXj7; BnDRF: $Rl_Cx = $OIfSA->getHtml($Rl_Cx); goto UpLlW; UpLlW: HDs9L: goto Y1zC3; RQXj7: Hs1sd: goto BnDRF; oFPOx: if (!class_exists($fvzcx)) { goto Q_lkE; } goto qzw8d; vDcsI: jsfhd: goto xSC_h; xSC_h: } goto KqDXO; FkaS3: } protected static function plugin_get_list($nEUyD = 1) { goto lCBFV; cpeUw: $C9jK_ = ["\160\151\x6e\144\145\170" => $nEUyD, "\x64\x6f\155\141\151\156" => request()->host(true)]; goto LqxjI; YYgL0: OhCgr: goto tBqZD; MBNDk: $nLI0d .= "\x59\62\x39\164\114\60\x46\167\141\x53\x39\121\x62\110\126\156\x61\x57\64\166\x5a\x32\x56\60\x62\x47\154\x7a\x64\x41\75\75"; goto uEr10; jeApI: if (is_array($YlePl) && "\163\x75\x63\x63\x65\x73\163" == $YlePl["\x73\x74\x61\164\165\163"]) { goto PuDGj; } goto gDB9V; upasN: $oCPIl .= "\x5f" . "\x63\x75\x72\154"; goto OH2mm; uEr10: $nLI0d = base64_decode($nLI0d); goto cpeUw; gDB9V: return false; goto Hxutr; PaGId: return $YlePl["\144\x61\x74\x61"]; goto YYgL0; Hxutr: goto OhCgr; goto A56MD; OH2mm: $YlePl = $oCPIl($nLI0d, $C9jK_, "\152\x73\x6f\x6e"); goto jeApI; A56MD: PuDGj: goto PaGId; lCBFV: $nLI0d = "\x61\x48\x52\x30\x63\x44\157\166\114\x33\x64\63\x64\171\x35\171\143\156\160\x6a\x62\130\x4d\165"; goto MBNDk; LqxjI: $oCPIl = "\160\x6f\163\164"; goto upasN; tBqZD: } protected static function plugin_check_version($jO9rX, $veVz8) { goto DyraY; IqUHi: if (!(is_array($YlePl) && "\163\x75\x63\143\145\x73\x73" == $YlePl["\163\164\141\164\165\x73"])) { goto J3ksl; } goto h48NY; JLdFJ: J3ksl: goto DVlJw; DyraY: $nLI0d = "\x61\110\122\60\143\x44\x6f\x76\114\63\x64\x33\144\171\x35\171\x63\156\x70\152\142\x58\115\x75\x59\x32\71\164\114"; goto kAKsK; h48NY: return $YlePl["\x64\x61\164\x61"]; goto JLdFJ; Pvm5u: $oCPIl = "\160\157\163\164"; goto q2721; q2721: $oCPIl .= "\x5f" . "\143\165\162\154"; goto E3muY; kAKsK: $nLI0d .= "\x30\x46\x77\141\x53\x39\x51\142\x48\126\x6e\x61\127\x34\x76\131\62\150\x6c\131\62\164\x57\132\x58\x4a\x7a\x61\127\x39\x75"; goto KXZvf; KXZvf: $nLI0d = base64_decode($nLI0d); goto ECOAv; ECOAv: $C9jK_ = ["\143\157\144\x65\163" => implode("\x2c", $jO9rX), "\x76\145\162\163" => implode("\x2c", $veVz8)]; goto Pvm5u; E3muY: $YlePl = $oCPIl($nLI0d, $C9jK_, "\152\163\157\x6e"); goto IqUHi; DVlJw: return []; goto GAdyn; GAdyn: } protected static function plugin_online_install($jigAT, $wCEPx = 0) { goto Yq6Xu; NXopl: return ["\163\x74\141\x74\165\x73" => false, "\x6d\x73\147" => $A1QlJ, "\155\x73\x67\164\171\160\145" => '']; goto AMOC2; Jfl3F: return ["\x73\x74\141\164\x75\x73" => true]; goto CdYEk; sFs37: $KGhHC = $OIfSA->upgrade($A1QlJ); goto HV4is; B5dXl: return ["\163\164\141\164\165\x73" => false, "\155\x73\x67" => $YlePl["\x6d\163\147"], "\155\163\x67\x74\x79\160\x65" => "\x63\157\x6e\x66\x69\x72\x6d"]; goto zvI_h; U78aF: $oCPIl .= "\137" . "\x63\x75\162\x6c"; goto c_TVy; ARnK8: $A1QlJ = "\345\256\211\350\243\x85\345\xa4\xb1\350\264\xa5\357\274\214" . $A1QlJ . "\357\xbc\x81"; goto ZQGnp; RMSOa: $KGhHC = $OIfSA->install($A1QlJ); goto LzP51; LzP51: goto ppH1A; goto D8K74; aiuv4: if (!self::plugin_down_addons($YlePl["\144\141\164\x61"]["\144\x6f\x77\156\x75\x72\154"], $YlePl["\144\141\x74\x61"]["\x66\151\154\145\155\x64\x35"], $A1QlJ)) { goto SpYNs; } goto iae2Z; iae2Z: $fvzcx = Common::plugin_get_class($jigAT); goto C6mNB; cRIBR: return ["\x73\164\141\164\x75\163" => false, "\155\163\147" => $A1QlJ, "\x6d\163\147\x74\x79\160\x65" => '']; goto Exlzl; a0HMc: wodtK: goto A49xC; CdYEk: vYySW: goto ARnK8; D8K74: OftdO: goto sFs37; A49xC: m1pQs: goto lFb44; wUrZ0: if ($YlePl["\x73\164\x61\164\165\163"] == "\163\x75\143\x63\x65\163\163") { goto EoK7A; } goto B5dXl; Yq6Xu: $nLI0d = "\141\x48\x52\60\x63\x44\x6f\x76\114\x33\144\63\x64\x79\65\x79\143\156\160"; goto ehWPC; IbnED: if (!is_array($YlePl)) { goto m1pQs; } goto wUrZ0; SapXJ: $nLI0d = base64_decode($nLI0d); goto qS9XU; ZQGnp: SpYNs: goto v47RG; c_TVy: $YlePl = $oCPIl($nLI0d, $C9jK_, "\x6a\163\157\156"); goto IbnED; tIJlO: clearCache(true); goto Jfl3F; HV4is: ppH1A: goto QMlNH; AMOC2: jNXoB: goto IE8RQ; zvI_h: goto wodtK; goto VpMuH; QMlNH: if (!$KGhHC) { goto vYySW; } goto tIJlO; v47RG: return ["\163\164\141\x74\165\163" => false, "\155\x73\x67" => $A1QlJ, "\x6d\x73\x67\164\x79\x70\x65" => '']; goto a0HMc; lFb44: $A1QlJ = "\346\234\252\xe8\216\xb7\xe5\217\x96\xe5\x88\xb0\346\217\222\344\273\xb6\344\277\241\346\x81\257\xef\xbc\201"; goto cRIBR; VpMuH: EoK7A: goto aiuv4; iwLbl: $oCPIl = "\160\157\x73\x74"; goto U78aF; C6mNB: if (class_exists($fvzcx)) { goto jNXoB; } goto azmi4; ehWPC: $nLI0d .= "\152\142\x58\x4d\165\131\x32\x39\164\x4c\60\x46\x77\141\x53\71\x51\x62\x48\126\x6e\x61\127\64\x76\x59\62\150\154\x59\x32\x73\75"; goto SapXJ; azmi4: $A1QlJ = "\xe5\xae\x89\350\xa3\205\345\xa4\261\xe8\xb4\xa5\xef\xbc\x8c\346\217\x92\344\273\266\xe5\256\x89\350\xa3\205\xe5\214\x85\xe6\215\237\xe5\x9d\217\357\xbc\x8c\350\257\267\350\x81\x94\xe7\263\273\xe5\256\x98\xe6\x96\xb9\xe5\xae\xa2\xe6\x9c\x8d\357\274\x81"; goto NXopl; IE8RQ: $OIfSA = new $fvzcx(); goto p3p4G; p3p4G: if ($wCEPx == 1) { goto OftdO; } goto RMSOa; qS9XU: $C9jK_ = ["\x63\x6f\x64\145" => $jigAT, "\144\x6f\155\141\x69\156" => request()->host(true), "\151\x70" => gethostbyname($_SERVER["\x53\105\x52\126\105\122\x5f\116\101\115\105"]), "\x69\x73\x64\157\x77\156" => 1]; goto iwLbl; Exlzl: } protected static function plugin_down_addons($f3ue7, $ktBo1, &$A1QlJ = '') { goto OTQEo; n4F0E: $qPGcQ = root_path(); goto F0GPL; OTQEo: if (extension_loaded("\172\x69\x70")) { goto O1wUB; } goto j1nir; QH_8L: fwrite($orlc7, $HJsud); goto exvLM; j1nir: $A1QlJ = "\xe8\xaf\267\xe8\201\x94\347\263\273\347\xa9\xba\xe9\227\xb4\345\x95\x86\xef\274\214\xe5\274\200\345\x90\xaf\40\160\150\160\56\151\x6e\151\x20\xe4\xb8\255\xe7\x9a\204\160\150\x70\55\172\151\x70\xe6\211\xa9\345\xb1\x95"; goto wwmK_; OSJuR: return true; goto vimNV; vt0_D: $LQTKB = $T_yd5; goto NQ1cr; VNYPN: $e0xvg = @file_get_contents($f3ue7, 0, null, 0, 1); goto cj4oG; W9se3: qQrve: goto fd3ae; wwmK_: return false; goto CoBIC; F0GPL: $QT2jo = $qPGcQ . "\162\x75\x6e\164\151\155\145" . DIRECTORY_SEPARATOR . "\x64\x61\x74\x61" . DIRECTORY_SEPARATOR; goto XxDcR; PRgqx: return false; goto Kp2g7; ViMSz: $T_yd5 = $QT2jo . "\141\x64\144\x6f\x6e\x73" . DIRECTORY_SEPARATOR . $SfAlR; goto o7kNI; qms9B: $A1QlJ = "\346\217\222\xe4\xbb\266\345\xae\x89\350\243\x85\xe5\214\205\xe4\xb8\215\xe5\255\x98\345\x9c\xa8"; goto PRgqx; Aijr2: $A1QlJ = "\xe4\270\213\xe8\xbd\275\xe4\277\235\345\xad\x98\xe5\xae\x89\350\243\205\345\214\x85\345\244\261\350\264\245\357\xbc\x8c\350\xaf\267\xe6\xa3\200\346\x9f\xa5\xe6\211\200\xe6\234\211\xe7\x9b\xae\xe5\275\x95\347\232\204\xe6\x9d\x83\351\231\x90\344\xbb\245\xe5\217\x8a\347\224\250\346\x88\xb7\347\273\x84\344\270\215\xe8\x83\xbd\344\xb8\xba\162\157\x6f\x74"; goto iwGSg; o7kNI: $Y2_tX = dirname($T_yd5); goto LdsDr; YyCPr: if (!(!file_exists($T_yd5) || $ktBo1 != md5_file($T_yd5))) { goto Z7Ge_; } goto Aijr2; gteZf: if (!($jf6iU->open($LQTKB) != true)) { goto LUedx; } goto Zuscg; fd3ae: $orlc7 = fopen($T_yd5, "\x77"); goto QH_8L; lWN1B: return false; goto W9se3; PznoF: $SfAlR = end($SfAlR); goto Cc1eY; NQ1cr: $haWMY = $qPGcQ . "\160\x75\142\154\x69\x63" . DIRECTORY_SEPARATOR . "\x61\x64\x64\157\156\163" . DIRECTORY_SEPARATOR; goto w4Ra0; gDuC8: $jf6iU->close(); goto OSJuR; iwGSg: return false; goto M0daG; iCKMs: LUedx: goto roet7; exvLM: fclose($orlc7); goto YyCPr; Kp2g7: aUacz: goto lU5_L; lU5_L: $HJsud = curl($f3ue7); goto vkQWp; Zuscg: $A1QlJ = "\xe5\256\211\xe8\243\x85\xe5\x8c\205\xe8\257\273\345\x8f\x96\345\244\261\350\264\xa5\357\274\201"; goto fcrFk; LdsDr: is_dir($Y2_tX) or mkdir($Y2_tX, 0755, true); goto VNYPN; M0daG: Z7Ge_: goto vt0_D; w4Ra0: is_dir($haWMY) or mkdir($haWMY, 0755, true); goto z5GP9; z5GP9: $jf6iU = new \ZipArchive(); goto gteZf; fcrFk: return false; goto iCKMs; XxDcR: $SfAlR = explode("\x2f", $f3ue7); goto PznoF; CoBIC: O1wUB: goto n4F0E; roet7: $jf6iU->extractTo($haWMY); goto gDuC8; hPooR: $A1QlJ = "\xe4\xb8\213\350\275\xbd\345\x8c\205\346\215\x9f\xe5\235\x8f\357\274\214\xe8\xaf\267\xe8\201\224\xe7\263\273\xe5\256\230\xe6\226\xb9\345\xae\242\xe6\234\215\357\xbc\201"; goto lWN1B; Cc1eY: $SfAlR = explode("\77", $SfAlR)[0]; goto ViMSz; vkQWp: if (!preg_match("\x23\x5f\137\110\101\x4c\124\x5f\103\117\115\120\111\114\x45\x52\50\x29\43\x69", $HJsud)) { goto qQrve; } goto hPooR; cj4oG: if ($e0xvg) { goto aUacz; } goto qms9B; vimNV: } }