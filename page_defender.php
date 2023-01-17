<?php
    // 页面内容保护器 - Page Defender
    // Version: 1.2
    // By Hazx.

    /*
    要在调用的页面头部引入本程序
    require './page_defender.php';

    如果你想让防护页面显示自定义标题，请这样引入
    $page_def_cnf_title = "Page Defender";
    require './page_defender.php';
    */

    // 配置
    $page_def_cnf_key = "xxxxxxxxxxxxxxx"; // 加密秘钥
    $page_def_cnf_calc_prob_block = 8; // 生成的计算公式长度，必须是大于2的整数
    $page_def_cnf_calc_prob_num = 16; // 生成的计算数字大小，必须是大于1的整数
    $page_def_cnf_calc_q_c_min = 3; // 随机生成 js 计算题的最小数量
    $page_def_cnf_calc_q_c_max = 8; // 随机生成 js 计算题的最大数量
    



    // 禁止直接访问
    if(strpos($_SERVER['SCRIPT_NAME'], 'page_defender.php') != false){
        echo "ERROR 400 - Bad Request";
        die;
    }

    // 生成链接签名  不传参  返回：签名（字符串）
    function page_def_make_upt(){
        global $page_def_cnf_key;
        $time_end = time() + rand(30, 60);
        $sign = md5($page_def_cnf_key."&".$time_end."&".$_SERVER['REQUEST_URI'], false);
        $upt = substr($sign, 12, 8).$time_end;

        return $upt;
    }

    // 生成假的链接签名  传参：签名（字符串）、js答案（整数）  返回：假签名（字符串）
    function page_def_make_upt_fake($upt, $js_answer){
        $js_answer_h = $js_answer + strlen($_SERVER["HTTP_HOST"]);
        if($js_answer_h < 0){
            $js_answer_h = $js_answer_h * -1;
        }
        if($js_answer_h > 999999999){
            $js_answer_h = substr($js_answer_h, 1, 6);
        }
        $upt_fake = substr($upt, 0, 8).((int)substr($upt, 8) - $js_answer_h);

        return $upt_fake;
    }

    // 生成随机字符串  传参：位数  返回：字符串
    function page_def_make_str($length){
        if($length <= 0){
            echo "function page_def_make_str error.";
            die;
        }
        $res_str = "";
        for($i = $length; $i > 0; $i--){
            $letter_num = rand(1, 52);
            if($letter_num <= 26){
                $letter_num += 64;
            }else{
                $letter_num = $letter_num - 26 + 96;
            }
            // 排除相似字符 OoIl
            if($letter_num == 79 || $letter_num == 111 || $letter_num == 73 || $letter_num == 108){
                $i++;
            }else{
                $res_str = $res_str.chr($letter_num);
            }
        }

        return $res_str;
    }

    // 生成数学计算题  不传参  返回数组：字符串题目、整数答案
    function page_def_make_calc_prob(){
        global $page_def_cnf_calc_prob_block;
        global $page_def_cnf_calc_prob_num;
        if($page_def_cnf_calc_prob_block < 3) $page_def_cnf_calc_prob_block = 3;
        if($page_def_cnf_calc_prob_num < 2) $page_def_cnf_calc_prob_num = 2;
        $clac_prob = "";
        $clac_prob_a = 0;
        $clac_symbol = "";
        $clac_prob_block = rand(2, (int)$page_def_cnf_calc_prob_block); // 计算公式长度
        for($i = $clac_prob_block; $i > 0; $i--){
            $clac_prob_num = rand(1, (int)$page_def_cnf_calc_prob_num); // 计算数字大小
            if($i == $clac_prob_block){
                $clac_prob = $clac_prob_num;
                $clac_prob_a = $clac_prob_num;
            }else{
                $clac_prob = $clac_prob.$clac_prob_num;
                if($clac_symbol == "+"){
                    $clac_prob_a += $clac_prob_num;
                }else{
                    $clac_prob_a -= $clac_prob_num;
                }
            }
            if($i > 1){
                if(rand(1, 2) == 1){
                    $clac_prob = $clac_prob."+";
                    $clac_symbol = "+";
                }else{
                    $clac_prob = $clac_prob."-";
                    $clac_symbol = "-";
                }
            }
        }
        $res_arr = array($clac_prob, $clac_prob_a);
    
        return $res_arr;
    }

    // 生成 js 计算单元   传参：整数   返回：字符串
    function page_def_make_js_calc_unit($js_calc_unit_num){
        $js_calc_unit_num = (int)$js_calc_unit_num;
        $js_calc_unit = "";
        if($js_calc_unit_num <= 10){
            if(rand(1, 2) == 1 && $js_calc_unit_num >= 3){
                $js_calc_unit_num2 = rand(1, $js_calc_unit_num - 1);
                $js_calc_unit_num = $js_calc_unit_num - $js_calc_unit_num2;
                for($i = $js_calc_unit_num; $i > 0; $i--){
                    $js_calc_unit = $js_calc_unit.'+!+[]';
                }
                $js_calc_unit = '+(+(['.$js_calc_unit.']))+(+([';
                for($i = $js_calc_unit_num2; $i > 0; $i--){
                    $js_calc_unit = $js_calc_unit.'+!+[]';
                }
                $js_calc_unit = $js_calc_unit.']))';
            }else{
                for($i = $js_calc_unit_num; $i > 0; $i--){
                    $js_calc_unit = $js_calc_unit.'+!+[]';
                }
                $js_calc_unit = '+('.$js_calc_unit.')';
            }
        }else{
            $js_calc_unit_num_len = strlen($js_calc_unit_num);
            for($i = 0; $i < $js_calc_unit_num_len; $i++){
                $js_calc_unit_tmp = "";
                for($j = substr($js_calc_unit_num, $i, 1); $j > 0; $j--){
                    $js_calc_unit_tmp = $js_calc_unit_tmp.'+!+[]';
                }
                $js_calc_unit = $js_calc_unit.'+['.$js_calc_unit_tmp.']';
            }
            $js_calc_unit_len = strlen($js_calc_unit);
            $js_calc_unit = substr($js_calc_unit, 1, $js_calc_unit_len - 1);
        }
        
        $js_calc_unit = "(+($js_calc_unit))";

        return $js_calc_unit;
    }

    // 生成单条 js 计算题  不传参   返回数组：字符串题目、整数答案、字符串题目（数字原题，用于调试）
    function page_def_make_js_calc_prob(){
        $calc_prob = page_def_make_calc_prob();
        $calc_prob_len = strlen($calc_prob[0]);
        $js_calc_prob = "";
        $tmp_num = "";
        for($i = 0; $i < $calc_prob_len; $i++){
            $tmp_str = substr($calc_prob[0], $i, 1);
            if(is_numeric($tmp_str)){
                $tmp_num = $tmp_num.$tmp_str;
            }else{
                $js_calc_prob = $js_calc_prob.page_def_make_js_calc_unit((int)$tmp_num).$tmp_str;
                $tmp_num = "";
            }
        }
        $js_calc_prob = $js_calc_prob.page_def_make_js_calc_unit((int)$tmp_num);
        $res_js_calc_prob = array($js_calc_prob, $calc_prob[1], $calc_prob[0]);

        return $res_js_calc_prob;
    }

    // 生成复杂 js 计算题  不传参   返回数组：变量名1、变量名2、字符串题目、整数答案
    function page_def_make_js_calc_prob_code(){
        global $page_def_cnf_calc_q_c_min;
        global $page_def_cnf_calc_q_c_max;
        $js_calc_ver_name1 = page_def_make_str(rand(6, 12));
        $js_calc_ver_name2 = page_def_make_str(rand(6, 12));
        $js_calc_prob_init = page_def_make_js_calc_prob();
        $js_calc_prob = "var t,r,a,f, $js_calc_ver_name1={\"$js_calc_ver_name2\":$js_calc_prob_init[0]};";
        $js_calc_answer = $js_calc_prob_init[1];
        for($i = rand($page_def_cnf_calc_q_c_min, $page_def_cnf_calc_q_c_max); $i > 0; $i--){ // 计算题数量
            $calc_mode = rand(1, 3); // 随机加减乘
            $js_calc_prob_single = page_def_make_js_calc_prob();
            if($calc_mode == 1){
                $js_calc_prob = $js_calc_prob."$js_calc_ver_name1.$js_calc_ver_name2+=$js_calc_prob_single[0];";
                $js_calc_answer = $js_calc_answer + $js_calc_prob_single[1];
            }elseif($calc_mode == 2){
                $js_calc_prob = $js_calc_prob."$js_calc_ver_name1.$js_calc_ver_name2-=$js_calc_prob_single[0];";
                $js_calc_answer = $js_calc_answer - $js_calc_prob_single[1];
            }elseif($calc_mode == 3){
                $js_calc_prob = $js_calc_prob."$js_calc_ver_name1.$js_calc_ver_name2*=$js_calc_prob_single[0];";
                $js_calc_answer = $js_calc_answer * $js_calc_prob_single[1];
            }
        }
        // 数组：变量名1、变量名2、字符串题目、整数答案
        $res_js_calc_prob_code = array($js_calc_ver_name1, $js_calc_ver_name2, $js_calc_prob, $js_calc_answer);

        return $res_js_calc_prob_code;
    }

    // 页面主体
    function page_def_challenge_page(){
        global $page_def_cnf_title;
        if(!isset($page_def_cnf_title)) $page_def_cnf_title = "Page Defender | HMengine Module | Powered by Hazx";
        $js_code = page_def_make_js_calc_prob_code();
        // $js_code[0]：变量名1
        // $js_code[1]：变量名2
        // $js_code[2]：字符串题目
        // $js_code[3]：整数答案
        $js_answer = $js_code[3];
        $upt = page_def_make_upt();
        $upt_fake = page_def_make_upt_fake($upt, $js_answer);
?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1">
    <meta http-equiv="Cache-Control" content="no-siteapp" />
    <title><?php echo $page_def_cnf_title; ?></title>
    <script>
        (function(){
            var a = function() {
                try{
                    return !!window.addEventListener
                } catch(e) {
                    return !1
                }
            }, b = function(b, c) {
                a() ? document.addEventListener("DOMContentLoaded", b, c) : document.attachEvent("onreadystatechange", b)
            };
            b(function(){
                <?php echo $js_code[2]; ?>
                <?php // 这一段用来提取当前网页域名，最终 t=域名 ?>

                t = document.createElement("div");
                t.innerHTML="<a href='/'>x</a>";
                t = t.firstChild.href;
                r = t.match(/https?:\/\//)[0];
                t = t.substr(r.length);
                t = t.substr(0,t.length-1);
                a = document.getElementById("page-def-answer");
                f = document.getElementById("challenge-form");
                var upt = a.value;
                var as = <?php echo "$js_code[0].$js_code[1]"; ?> + t.length;
                if(as < 0) as = as * -1;
                if(as > 999999999) as = Math.ceil(as.toString().substr(1,6));
                a.value = upt.substr(0,8).toString()+(Math.ceil(upt.substr(8))+as);
                f.submit();
            }, false);
        })();
    </script>
</head>
<body>
    <table width="100%" height="100%" cellpadding="20">
        <tr>
            <td align="center" valign="middle">
                <div>
                    <noscript><h1 data-translate="turn_on_js" style="color:#bd2426;">请启用 JavaScript 功能，并刷新网页。</h1></noscript>
                    <form id="challenge-form" method="post">
                        <input type="hidden" id="page-def-answer" name="page_def_answer" value="<?php echo $upt_fake; ?>" />
                    </form>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>

<?php
    }

    // 验证访问行为  传参：用户传来的答案（字符串）  返回：yes/no/expired
    function page_def_verify($answer){
        global $page_def_cnf_key;
        global $page_def_cnf_upt_time;

        $answer_key = substr($answer, 0, 8);
        $answer_time = substr($answer, 8);
        $sign = md5($page_def_cnf_key."&".$answer_time."&".$_SERVER['REQUEST_URI'], false);
        if(substr($sign, 12, 8) != $answer_key){
            return "no"; // 签名验证不通过
        }else{
            $time = time();
            if($answer_time >= $time){
                return "yes"; // 签名验证通过
            }else{
                return "expired"; // 签名过期
            }
        }
    }



    if(!isset($_POST['page_def_answer'])){
        page_def_challenge_page();
        exit;
    }else{
        $page_def_js_answer = addslashes($_POST["page_def_answer"]);
        $page_def_verify_res = page_def_verify($page_def_js_answer);
        if($page_def_verify_res == "expired"){
            page_def_challenge_page();
            exit;
        }else if($page_def_verify_res == "no"){
            die;
        }
    }





