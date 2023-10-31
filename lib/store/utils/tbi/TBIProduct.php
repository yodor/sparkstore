<?php
    /* Начало на PHP кода за Кредитен Калкулатор TBI Bank */
    $tbi_mod_version = '3.1.0';
    $product_id = TBIData::$id; //задайте променливата на PHP, която определя продуктовия id
    $product_price = TBIData::$price; //задайте променливата на PHP, която определя продуктовата цена
    $product_quantity = TBIData::$quantity; //задайте променливата на PHP, която определя продуктовата бройка
    $product_name = TBIData::$name; //задайте променливата на PHP, която определя продуктовото име
    $prod_categories = null; //тази променлива е масив от категориите в които влиза Вашия продукт (незадължителна)
    $manufacturer_id = null; //тази променлива е идентификатор номерът на производителя на Вашия продукт (незадължителна)
    ///////////////////////////////////////////////////////////////////////////////////
    $unicid = TBIData::$store_uid;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
	curl_setopt($ch, CURLOPT_TIMEOUT, 6);
    curl_setopt($ch, CURLOPT_URL, 'https://tbibank.support/function/getparameters.php?cid='.$unicid);
    $paramstbi = json_decode(curl_exec($ch), true);
    curl_close($ch);

    //check result
    if (is_array($paramstbi) && isset($paramstbi["unicid"])) {


    $minprice_tbi = $paramstbi['tbi_minstojnost'];
    $maxprice_tbi = $paramstbi['tbi_maxstojnost'];
    $tbi_theme = $paramstbi['tbi_theme'];
	$tbi_zastrahovka_select = $paramstbi['tbi_zastrahovka_select'];
    $tbi_btn_color = '#e55a00;';
    if ($paramstbi['tbi_btn_theme'] == 'tbi'){
        $tbi_btn_color = '#e55a00;';
    }
    if ($paramstbi['tbi_btn_theme'] == 'tbi2'){
        $tbi_btn_color = '#00368a;';
    }
    if ($paramstbi['tbi_btn_theme'] == 'tbi3'){
        $tbi_btn_color = '#2b7953;';
    }
    if ($paramstbi['tbi_btn_theme'] == 'tbi4'){
        $tbi_btn_color = '#848789;';
    }
    $tbi_btn_theme = $paramstbi['tbi_btn_theme'];
    $tbi_btn_color = $tbi_btn_color;
	$tbi_custom_button_status = $paramstbi['tbi_custom_button_status'];
	$tbi_button_position = $paramstbi['tbi_button_position'];
	$tbi_zaglavie = $paramstbi['tbi_zaglavie'];

	$tpurcent = 12;
	if ($paramstbi['tbi_purcent_default'] == 1){
        $tpurcent = 3;
    }
	if ($paramstbi['tbi_purcent_default'] == 2){
        $tpurcent = 4;
    }
	if ($paramstbi['tbi_purcent_default'] == 3){
        $tpurcent = 6;
    }
	if ($paramstbi['tbi_purcent_default'] == 4){
        $tpurcent = 9;
    }
	if ($paramstbi['tbi_purcent_default'] == 5){
        $tpurcent = 12;
    }
	if ($paramstbi['tbi_purcent_default'] == 6){
        $tpurcent = 15;
    }
	if ($paramstbi['tbi_purcent_default'] == 7){
        $tpurcent = 18;
    }
	if ($paramstbi['tbi_purcent_default'] == 8){
        $tpurcent = 24;
    }
	if ($paramstbi['tbi_purcent_default'] == 9){
        $tpurcent = 30;
    }
	if ($paramstbi['tbi_purcent_default'] == 10){
        $tpurcent = 36;
    }
	// схема 10+1
	if ($paramstbi['tbi_purcent_default'] == 11){
        $tpurcent = 10;
    }
	// схема 10+1
	// схема 8-1
	if ($paramstbi['tbi_purcent_default'] == 12){
        $tpurcent = 7;
    }
	// схема 8-1
	// схема 13-2
	if ($paramstbi['tbi_purcent_default'] == 13){
        $tpurcent = 11;
    }
	// схема 13-2
	// схема 12-5
	if ($paramstbi['tbi_purcent_default'] == 14){
        $tpurcent = 14;
    }
	// схема 12-5
	if ($paramstbi['tbi_purcent_default'] == 15){
        $tpurcent = 42;
    }
	if ($paramstbi['tbi_purcent_default'] == 16){
        $tpurcent = 48;
    }

	if (($paramstbi['tbi_5m_purcent_default'] == 1) && (($paramstbi['tbi_5m'] == "Yes") || ($paramstbi['tbi_5m_pv'] == "Yes"))){
        $tpurcent = 5;
    }

	// схема 10+1
	if (($paramstbi['tbi_purcent_default'] == 11) || ((($paramstbi['tbi_purcent_default'] == 2) && (($paramstbi['tbi_4m'] == "Yes") || ($paramstbi['tbi_4m_pv'] == "Yes"))) || (($paramstbi['tbi_5m_purcent_default'] == 1) && (($paramstbi['tbi_5m'] == "Yes") || ($paramstbi['tbi_5m_pv'] == "Yes"))) || (($paramstbi['tbi_purcent_default'] == 3) && (($paramstbi['tbi_6m'] == "Yes") || ($paramstbi['tbi_6m_pv'] == "Yes"))) )){
        $oskapiavane_12 = 0.015;
        $vnoska = $product_price / $tpurcent;
    }else{
        if ($paramstbi['tbi_purcent_default'] == 14){
            $oskapiavane_12 = 0.019597;
            $vnoska = (($product_price - ($product_price * 0.10)) * (1 + $oskapiavane_12 * 12)) / 12;
        }else{
            $meseci = "tbi_" . $tpurcent . "m_purcent";
            $oskapiavane_12 = 0.015;
            if (isset($paramstbi["$meseci"])){
                if ($paramstbi["$meseci"]){
                    if (is_numeric($paramstbi["$meseci"])){
                        $oskapiavane_12 = $paramstbi["$meseci"] / 100;
                    }
                }
            }
            // 0.5% за >= 5000 лв. 0.83 за >= 10000
            if (floatval($product_price) >= 5000 && floatval($product_price) < 10000){
                if ($paramstbi['tbi_over_5000'] == 'Yes'){
                    switch ($tpurcent) {
                        case 3:
                            $oskapiavane_12 = 0.0100059;
                            break;
                        case 4:
                            $oskapiavane_12 = 0.0094036;
                            break;
                        case 6:
                            $oskapiavane_12 = 0.0088197;
                            break;
                        case 9:
                            $oskapiavane_12 = 0.0084612;
                            break;
                        case 12:
                            $oskapiavane_12 = 0.0083096;
                            break;
                        case 15:
                            $oskapiavane_12 = 0.0082406;
                            break;
                        case 18:
                            $oskapiavane_12 = 0.0082131;
                            break;
                        case 24:
                            $oskapiavane_12 = 0.0082198;
                            break;
                        case 30:
                            $oskapiavane_12 = 0.0082675;
                            break;
                        case 36:
                            $oskapiavane_12 = 0.0083355;
                            break;
                        case 42:
                            $oskapiavane_12 = 0.0084148;
                            break;
                        case 48:
                            $oskapiavane_12 = 0.0085009;
                            break;
                    }
                }
            }else {
                if (floatval($product_price) >= 10000){
                    if ($paramstbi['tbi_over_5000'] == 'Yes'){
                        switch ($tpurcent) {
                            case 3:
                                $oskapiavane_12 = 0.0060731;
                                break;
                            case 4:
                                $oskapiavane_12 = 0.0057021;
                                break;
                            case 6:
                                $oskapiavane_12 = 0.0053379;
                                break;
                            case 9:
                                $oskapiavane_12 = 0.0051065;
                                break;
                            case 12:
                                $oskapiavane_12 = 0.0050011;
                                break;
                            case 15:
                                $oskapiavane_12 = 0.0049460;
                                break;
                            case 18:
                                $oskapiavane_12 = 0.0049162;
                                break;
                            case 24:
                                $oskapiavane_12 = 0.0048942;
                                break;
                            case 30:
                                $oskapiavane_12 = 0.0048973;
                                break;
                            case 36:
                                $oskapiavane_12 = 0.0049130;
                                break;
                            case 42:
                                $oskapiavane_12 = 0.0049358;
                                break;
                            case 48:
                                $oskapiavane_12 = 0.004963;
                                break;
                        }
                    }
                }
            }
            // 0.5% за >= 5000 лв. 0.83 за >= 10000

            $vnoska = ($product_price * (1 + $oskapiavane_12 * $tpurcent)) / $tpurcent;
        }
    }
	// схема 10+1

    if ($paramstbi['tbi_4m'] == "Yes"){
        if (is_numeric($product_id)){
            $categories = explode('_', $paramstbi['tbi_4m_categories']);
            if (isProductInCategories($categories, $prod_categories)){
                $is4m = 'Yes';
            }else{
                $manufacturers = explode('_', $paramstbi['tbi_4m_manufacturers']);
                if (($manufacturer_id != null) && in_array($manufacturer_id, $manufacturers)){
                    $is4m = 'Yes';
                }else{
                    if ((doubleval($paramstbi['tbi_4m_min']) <= $product_price) && ($product_price <= doubleval($paramstbi['tbi_4m_max']))){
                        $is4m = 'Yes';
                    }else{
                        $is4m = 'No';
                    }
                }
            }
        }else{
            $is4m = 'No';
        }
    }else{
        $is4m = 'No';
    }

    if ($paramstbi['tbi_4m_pv'] == "Yes"){
        if (is_numeric($product_id)){
            $categories = explode('_', $paramstbi['tbi_4m_categories']);
            if (isProductInCategories($categories, $prod_categories)){
                $is4m_pv = 'Yes';
            }else{
                $manufacturers = explode('_', $paramstbi['tbi_4m_manufacturers']);
                if (($manufacturer_id != null) && in_array($manufacturer_id, $manufacturers)){
                    $is4m_pv = 'Yes';
                }else{
                    if ((doubleval($paramstbi['tbi_4m_min']) <= $product_price) && ($product_price <= doubleval($paramstbi['tbi_4m_max']))){
                        $is4m_pv = 'Yes';
                    }else{
                        $is4m_pv = 'No';
                    }
                }
            }
        }else{
            $is4m_pv = 'No';
        }
    }else{
        $is4m_pv = 'No';
    }

    if ($paramstbi['tbi_5m'] == "Yes"){
        if (is_numeric($product_id)){
            $categories = explode('_', $paramstbi['tbi_5m_categories']);
            if (isProductInCategories($categories, $prod_categories)){
                $is5m = 'Yes';
            }else{
                $manufacturers = explode('_', $paramstbi['tbi_5m_manufacturers']);
                if (($manufacturer_id != null) && in_array($manufacturer_id, $manufacturers)){
                    $is5m = 'Yes';
                }else{
                    if ((doubleval($paramstbi['tbi_5m_min']) <= $product_price) && ($product_price <= doubleval($paramstbi['tbi_5m_max']))){
                        $is5m = 'Yes';
                    }else{
                        $is5m = 'No';
                    }
                }
            }
        }else{
            $is5m = 'No';
        }
    }else{
        $is5m = 'No';
    }

    if ($paramstbi['tbi_5m_pv'] == "Yes"){
        if (is_numeric($product_id)){
            $categories = explode('_', $paramstbi['tbi_5m_categories']);
            if (isProductInCategories($categories, $prod_categories)){
                $is5m_pv = 'Yes';
            }else{
                $manufacturers = explode('_', $paramstbi['tbi_5m_manufacturers']);
                if (($manufacturer_id != null) && in_array($manufacturer_id, $manufacturers)){
                    $is5m_pv = 'Yes';
                }else{
                    if ((doubleval($paramstbi['tbi_5m_min']) <= $product_price) && ($product_price <= doubleval($paramstbi['tbi_5m_max']))){
                        $is5m_pv = 'Yes';
                    }else{
                        $is5m_pv = 'No';
                    }
                }
            }
        }else{
            $is5m_pv = 'No';
        }
    }else{
        $is5m_pv = 'No';
    }

    if ($paramstbi['tbi_6m'] == "Yes"){
        if (is_numeric($product_id)){
            $categories = explode('_', $paramstbi['tbi_6m_categories']);
            if (isProductInCategories($categories, $prod_categories)){
                $is6m = 'Yes';
            }else{
                $manufacturers = explode('_', $paramstbi['tbi_6m_manufacturers']);
                if (($manufacturer_id != null) && in_array($manufacturer_id, $manufacturers)){
                    $is6m = 'Yes';
                }else{
                    if ((doubleval($paramstbi['tbi_6m_min']) <= $product_price) && ($product_price <= doubleval($paramstbi['tbi_6m_max']))){
                        $is6m = 'Yes';
                    }else{
                        $is6m = 'No';
                    }
                }
            }
        }else{
            $is6m = 'No';
        }
    }else{
        $is6m = 'No';
    }

    if ($paramstbi['tbi_6m_pv'] == "Yes"){
        if (is_numeric($product_id)){
            $categories = explode('_', $paramstbi['tbi_6m_categories']);
            if (isProductInCategories($categories, $prod_categories)){
                $is6m_pv = 'Yes';
            }else{
                $manufacturers = explode('_', $paramstbi['tbi_6m_manufacturers']);
                if (($manufacturer_id != null) && in_array($manufacturer_id, $manufacturers)){
                    $is6m_pv = 'Yes';
                }else{
                    if ((doubleval($paramstbi['tbi_6m_min']) <= $product_price) && ($product_price <= doubleval($paramstbi['tbi_6m_max']))){
                        $is6m_pv = 'Yes';
                    }else{
                        $is6m_pv = 'No';
                    }
                }
            }
        }else{
            $is6m_pv = 'No';
        }
    }else{
        $is6m_pv = 'No';
    }

    if ($paramstbi['tbi_9m'] == "Yes"){
        if (is_numeric($product_id)){
            $categories = explode('_', $paramstbi['tbi_9m_categories']);
            if (isProductInCategories($categories, $prod_categories)){
                $is9m = 'Yes';
            }else{
                $manufacturers = explode('_', $paramstbi['tbi_9m_manufacturers']);
                if (($manufacturer_id != null) && in_array($manufacturer_id, $manufacturers)){
                    $is9m = 'Yes';
                }else{
                    if ((doubleval($paramstbi['tbi_9m_min']) <= $product_price) && ($product_price <= doubleval($paramstbi['tbi_9m_max']))){
                        $is9m = 'Yes';
                    }else{
                        $is9m = 'No';
                    }
                }
            }
        }else{
            $is9m = 'No';
        }
    }else{
        $is9m = 'No';
    }

    if ($paramstbi['tbi_9m_pv'] == "Yes"){
        if (is_numeric($product_id)){
            $categories = explode('_', $paramstbi['tbi_9m_categories']);
            if (isProductInCategories($categories, $prod_categories)){
                $is9m_pv = 'Yes';
            }else{
                $manufacturers = explode('_', $paramstbi['tbi_9m_manufacturers']);
                if (($manufacturer_id != null) && in_array($manufacturer_id, $manufacturers)){
                    $is9m_pv = 'Yes';
                }else{
                    if ((doubleval($paramstbi['tbi_9m_min']) <= $product_price) && ($product_price <= doubleval($paramstbi['tbi_9m_max']))){
                        $is9m_pv = 'Yes';
                    }else{
                        $is9m_pv = 'No';
                    }
                }
            }
        }else{
            $is9m_pv = 'No';
        }
    }else{
        $is9m_pv = 'No';
    }

	// shema 101
	if ($paramstbi['tbi_pokazi']  & 1024){
        $is101 = 'Yes';
    }else{
        $is101 = 'No';
    }
	// shema 101
	// shema 8-1
	if ($paramstbi['tbi_pokazi']  & 2048){
        $is81 = 'Yes';
    }else{
        $is81 = 'No';
    }
	// shema 8-1
	// shema 13-2
	if ($paramstbi['tbi_pokazi']  & 4096){
        $is112 = 'Yes';
    }else{
        $is112 = 'No';
    }
	// shema 13-2
	// shema 12-5
	if ($paramstbi['tbi_pokazi']  & 8192){
        $is13 = 'Yes';
    }else{
        $is13 = 'No';
    }
	// shema 12-5

	if ($paramstbi['tbi_taksa_categories'] != ""){
        $cats = explode('_', $paramstbi['tbi_taksa_categories']);
        if (isProductInCategories($cats, $prod_categories)){
            $isTaksa = 'Yes';
        }else{
            $isTaksa = 'No';
        }
    }else{
        $isTaksa = 'Yes';
    }

	//test 0%
	if ((($is4m == 'Yes' || $is4m_pv == 'Yes') && $paramstbi['tbi_purcent_default'] == 2) || (($is6m == 'Yes' || $is6m_pv == 'Yes') && $paramstbi['tbi_purcent_default'] == 3) || (($is9m == 'Yes' || $is9m_pv == 'Yes') && $paramstbi['tbi_purcent_default'] == 4)){
        $iszerrolihva = 'Yes';
    }else{
        $iszerrolihva = 'No';
    }

	if (($paramstbi['tbi_status'] == 'Yes') && (($product_price  > $minprice_tbi) && ($product_price  < $maxprice_tbi))){
        ?>
        <?php if ($tbi_zaglavie != ''){ ?>
            <table cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td class="tbi_button_div_table_old">
                        <div class="tbi_button_div_txt_old">
                            <?php echo $tbi_zaglavie; ?>
                        </div>
                    </td>
                </tr>
            </table>
        <?php } ?>
        <?php if ($tbi_custom_button_status == 'Yes'){ ?>
            <?php
            $meseci = $tpurcent;
            if ($tpurcent == 14){
                $meseci = 12;
            }
            if (!isset($is_vnoska)) {
                $is_vnoska = false;
            }
            ?>
            <table cellspacing="0" cellpadding="0" border="0">
                <?php if ($tbi_button_position == 1){ ?>
                    <tr>
                        <td style="padding:5px !important; background:transparent !important;">
                            <?php if ($is_vnoska){ ?>
                                <div style="color:<?php echo $tbi_btn_color; ?>;font-weight:bold;">
                                    <?php echo $meseci; ?> x <?php echo number_format($vnoska, 2, ".", ""); ?> лв.
                                </div>
                            <?php } ?>
                        </td>
                        <td style="padding:5px !important; background:transparent !important;">
                            <img id="btn_tbiapi" style="cursor:pointer;" src="https://tbibank.support/calculators/assets/img/custom_buttons/<?php echo $unicid; ?>.png"
                                 onMouseOver="this.src='https://tbibank.support/calculators/assets/img/custom_buttons/<?php echo $unicid; ?>_hover.png';"
                                 onMouseOut="this.src='https://tbibank.support/calculators/assets/img/custom_buttons/<?php echo $unicid; ?>.png';" >
                        </td>
                    </tr>
                <?php } ?>
                <?php if ($tbi_button_position == 2){ ?>
                    <tr>
                        <td style="padding:5px !important; background:transparent !important;">
                            <img id="btn_tbiapi" style="cursor:pointer;" src="https://tbibank.support/calculators/assets/img/custom_buttons/<?php echo $unicid; ?>.png"
                                 onMouseOver="this.src='https://tbibank.support/calculators/assets/img/custom_buttons/<?php echo $unicid; ?>_hover.png';"
                                 onMouseOut="this.src='https://tbibank.support/calculators/assets/img/custom_buttons/<?php echo $unicid; ?>.png';" >
                        </td>
                        <td style="padding:5px !important; background:transparent !important;">
                            <?php if ($is_vnoska){ ?>
                                <div style="color:<?php echo $tbi_btn_color; ?>;font-weight:bold;">
                                    <?php echo $meseci; ?> x <?php echo number_format($vnoska, 2, ".", ""); ?> лв.
                                </div>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
                <?php if ($tbi_button_position == 3){ ?>
                    <tr>
                        <td style="padding:5px !important; background:transparent !important;">
                            <img id="btn_tbiapi" style="cursor:pointer;" src="https://tbibank.support/calculators/assets/img/custom_buttons/<?php echo $unicid; ?>.png"
                                 onMouseOver="this.src='https://tbibank.support/calculators/assets/img/custom_buttons/<?php echo $unicid; ?>_hover.png';"
                                 onMouseOut="this.src='https://tbibank.support/calculators/assets/img/custom_buttons/<?php echo $unicid; ?>.png';" >
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:5px !important; background:transparent !important;">
                            <?php if ($is_vnoska){ ?>
                                <div style="color:<?php echo $tbi_btn_color; ?>;font-weight:bold;">
                                    <?php echo $meseci; ?> x <?php echo number_format($vnoska, 2, ".", ""); ?> лв.
                                </div>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        <?php }else{ ?>
            <table cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td class="tbi_table_button">
                        <div id="btn_tbiapi" class="tbi_button_div_old">
                            <div class="tbi_button_head_old"></div>
                            <div class="tbi_button_body_old">
                                <?php
                                $meseci = $tpurcent;
                                if ($tpurcent == 14){
                                    $meseci = 12;
                                }
                                ?>
                                <?php if ($iszerrolihva == 'Yes'){ ?>
                                    <span class="tbi_button_body_txt_old">0%</span> на <?php echo $meseci; ?> вноски по <?php echo number_format($vnoska, 2, ".", ""); ?> лв.
                                <?php }else{ ?>
                                    на <?php echo $meseci; ?> вноски по <?php echo number_format($vnoska, 2, ".", ""); ?> лв.
                                <?php } ?>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
            <script>
                const tbi_button_head_old = document.getElementsByClassName("tbi_button_head_old");
                tbi_button_head_old[0].onmouseover = function(){
                    document.getElementById("btn_tbiapi").style.background = "#EC7423";
                };
                tbi_button_head_old[0].onmouseout = function(){
                    document.getElementById("btn_tbiapi").style.background = "white";
                };
            </script>
        <?php } ?>
        <style>
            .tbi_table_button{
                padding-right:5px !important;
                padding-bottom:5px !important;
                padding-top:0px !important;
                border: 0px none white !important;
                background-color: transparent !important;
            }
            .tbi_button_mess_old{
                width:252px !important;
                white-space: nowrap !important;
                font-size:14px !important;
                color:#EC7423 !important;
                font-weight:bold !important;
            }
            .tbi_button_div_old{
                border:2px solid #ea6e0e !important;
                background: white;
                border-radius: 26px !important;
                cursor:pointer !important;
                width:232px !important;
                height:52px !important;
                box-sizing: border-box !important;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
            }
            .tbi_button_div_txt_old{
                width:252px !important;
                color:#EC7423 !important;
                font-size:14px !important;
                font-weight:bold !important;
                vertical-align: bottom !important;
                text-align:center !important;
                line-height: 20px !important;
            }
            .tbi_button_div_table_old{
                padding-right:5px;
                padding-top:0px;
                padding-bottom:0px;
            }
            .tbi_button_head_old{
                height: 30px !important;
                width: 220px !important;
                background-repeat: no-repeat !important;
                background-position: center !important;
                background: url('https://tbibank.support/calculators/assets/img/buttons/tbi_head_new.png');
            }
            .tbi_button_head_old:hover{
                background: url('https://tbibank.support/calculators/assets/img/buttons/tbi_head_hover_new.png');
            }
            .tbi_button_head_old:hover + .tbi_button_body_old{
                color:white !important;
            }
            .tbi_button_body_old{
                height:20px !important;
                text-align:center !important;
                color:#504f50 !important;
                font-size:14px !important;
                font-weight:normal !important;
                vertical-align: middle !important;
                line-height: 20px !important;
            }
        </style>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:400,100,300,500">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <link rel="stylesheet" href="https://tbibank.support/calculators/assets/css/<?php echo $tbi_theme; ?>">
        <div id="tbi_box" class="modal">
            <div class="modal-content">
                <div id="tbi_body" class="modal-body">
                </div>
            </div>
        </div>
        <script>
            var cid = '<?php echo $unicid; ?>';
            var tbi_box = document.getElementById('tbi_box');
            var tbi_btn_open = document.getElementById("btn_tbiapi");
            tbi_btn_open.onclick = function() {
                showTbiBoxHtml(cid, <?php echo $tpurcent; ?>, '<?php echo $tbi_zastrahovka_select; ?>', 0);
                tbi_box.style.display = "block";
            }
            function tbibuy(_comment, _pogasitelni_vnoski_input, _zastrahovka_input, _parva_input, _mesecna_vnoska_input, _gpr_input, _tbi_uslovia, _tbireklama){
                if (typeof(_tbi_uslovia) != 'undefined' && _tbi_uslovia != null && _tbi_uslovia.checked){
                    if (typeof(_tbireklama) != 'undefined' && _tbireklama != null && _tbireklama.checked){
                        showTbibuyHtml(cid, _comment.value, _pogasitelni_vnoski_input.value, _zastrahovka_input.value, _parva_input.value, _mesecna_vnoska_input.value, _gpr_input.value, _tbireklama.value);
                    }else{
                        showTbibuyHtml(cid, _comment.value, _pogasitelni_vnoski_input.value, _zastrahovka_input.value, _parva_input.value, _mesecna_vnoska_input.value, _gpr_input.value, 'No');
                    }
                }else{
                    alert('Моля съгласете се с обработката на личните Ви данни за да преминете към попълване на данни за клиента!');
                }
            }
            function tbisend(_name, _egn, _phone, _email, _address, _address2, _comment, _pogasitelni_vnoski_input, _zastrahovka_input, _parva_input, _mesecna_vnoska_input, _gpr_input, _tbireklama){
                showTbisendHtml(cid, _name, _egn, _phone, _email, _address, _address2, _comment, _pogasitelni_vnoski_input, _zastrahovka_input, _parva_input, _mesecna_vnoska_input, _gpr_input, _tbireklama);
            }
            function tbibuy_back(){
                showTbiBoxHtml(cid, <?php echo $tpurcent; ?>, '<?php echo $tbi_zastrahovka_select; ?>', 0);
            }
            function showTbiBoxHtml(param, _vnoski, _zastrahovka, _parva) {
                if (param.length == 0) {
                    document.getElementById("tbi_body").innerHTML = "";
                    return;
                } else {
                    var xmlhttp = new XMLHttpRequest();
                    xmlhttp.onreadystatechange = function() {
                        if (this.readyState == 4 && this.status == 200) {
                            document.getElementById("tbi_body").innerHTML = this.responseText;
                            document.getElementById("tbi_product_name").innerHTML = '<?php echo str_replace('"', '', str_replace("'", "", $product_name)); ?>';
                        }
                    };
                    var q = parseFloat('<?php echo $product_quantity; ?>');
                    if (isNaN(q) || (q == 0) || (q > 5)){
                        q = 1;
                    }
                    var priceall = parseFloat('<?php echo $product_price; ?>') * q;
                    var x = window.innerWidth || document.documentElement.clientWidth || document.getElementsByTagName('body')[0].clientWidth;
                    <?php if ($tbi_theme == 'style3.css') { ?>
                    var urlto = 'tbi_css.php';
                    <?php }else{ ?>
                    <?php if ($tbi_theme == 'style4.css'){ ?>
                    var urlto = 'tbi_short.php';
                    <?php }else{ ?>
                    if (x <= 1024){
                        var urlto = 'tbi_m.php';
                    }else{
                        var urlto = 'tbi_tab.php';
                    }
                    <?php } ?>
                    <?php } ?>
                    xmlhttp.open("GET", "https://tbibank.support/calculators/"+urlto+"?cid=" + param + "&is4m=<?php echo $is4m; ?>&is4m_pv=<?php echo $is4m_pv; ?>&is5m=<?php echo $is5m; ?>&is5m_pv=<?php echo $is5m_pv; ?>&is6m=<?php echo $is6m; ?>&is6m_pv=<?php echo $is6m_pv; ?>&is9m=<?php echo $is9m; ?>&is9m_pv=<?php echo $is9m_pv; ?>&is101=<?php echo $is101; ?>&is81=<?php echo $is81; ?>&is112=<?php echo $is112; ?>&is13=<?php echo $is13; ?>&isTaksa=<?php echo $isTaksa; ?>&price_input="+priceall+"&pogasitelni_vnoski_input="+_vnoski+"&zastrahovka_input="+_zastrahovka+"&parva_input="+_parva+"&tbi_mod_version=<?php echo $tbi_mod_version; ?>", true);
                    xmlhttp.send();
                }
            }
            function showTbibuyHtml(param, _comment, _pogasitelni_vnoski_input, _zastrahovka_input, _parva_input, _mesecna_vnoska_input, _gpr_input, _tbireklama) {
                if (param.length == 0) {
                    document.getElementById("tbi_body").innerHTML = "";
                    return;
                } else {
                    document.getElementById("tbi_body").innerHTML = "";
                    var xmlhttp = new XMLHttpRequest();
                    xmlhttp.onreadystatechange = function() {
                        if (this.readyState == 4 && this.status == 200) {
                            document.getElementById("tbi_body").innerHTML = this.responseText;
                            document.getElementById("tbi_product_name").innerHTML = 'Необходими данни за искане на стоков кредит';
                        }
                    };
                    var x = window.innerWidth || document.documentElement.clientWidth || document.getElementsByTagName('body')[0].clientWidth;
                    <?php if ($tbi_theme == 'style3.css') { ?>
                    var urlto = 'tbibuy_css.php';
                    <?php }else{ ?>
                    <?php if ($tbi_theme == 'style4.css'){ ?>
                    var urlto = 'tbibuy_short.php';
                    <?php }else{ ?>
                    if (x <= 1024){
                        var urlto = 'tbibuy_m.php';
                    }else{
                        var urlto = 'tbibuy_tab.php';
                    }
                    <?php } ?>
                    <?php } ?>
                    xmlhttp.open("GET", "https://tbibank.support/calculators/"+urlto+"?cid="+param+"&comment="+_comment+"&pogasitelni_vnoski_input="+_pogasitelni_vnoski_input+"&zastrahovka_input="+_zastrahovka_input+"&parva_input="+_parva_input+"&mesecna_vnoska_input="+_mesecna_vnoska_input+"&gpr_input="+_gpr_input+"&tbireklama="+_tbireklama, true);
                    xmlhttp.send();
                }
            }
            function showTbisendHtml(param, _name, _egn, _phone, _email, _address, _address2, _comment, _pogasitelni_vnoski_input, _zastrahovka_input, _parva_input, _mesecna_vnoska_input, _gpr_input, _tbireklama) {
                if (param.length == 0) {
                    document.getElementById("tbi_body").innerHTML = "";
                    return;
                } else {
                    document.getElementById("tbi_body").innerHTML = "";
                    var xmlhttp = new XMLHttpRequest();
                    xmlhttp.onreadystatechange = function() {
                        if (this.readyState == 4 && this.status == 200) {
                            document.getElementById("tbi_body").innerHTML = this.responseText;
                            document.getElementById("tbi_product_name").innerHTML = 'Изпращане на заявка за стоков кредит';
                        }
                    };
                    var _pq = parseFloat(<?php echo $product_quantity; ?>);
                    if (isNaN(_pq) || (_pq == 0) || (_pq > 5)){
                        _pq = 1;
                    }
                    var price1 = parseFloat('<?php echo $product_price; ?>');
                    var priceall = price1 * _pq;
                    var x = window.innerWidth || document.documentElement.clientWidth || document.getElementsByTagName('body')[0].clientWidth;
                    <?php if ($tbi_theme == 'style3.css') { ?>
                    var urlto = 'tbisend_css.php';
                    <?php }else{ ?>
                    <?php if ($tbi_theme == 'style4.css'){ ?>
                    var urlto = 'tbisend_short.php';
                    <?php }else{ ?>
                    if (x <= 1024){
                        var urlto = 'tbisend_m.php';
                    }else{
                        var urlto = 'tbisend.php';
                    }
                    <?php } ?>
                    <?php } ?>
                    xmlhttp.open("GET", "https://tbibank.support/calculators/"+urlto+"?cid="+param+"&name="+_name+"&egn="+_egn+"&phone="+_phone+"&email="+_email+"&address="+_address+"&address2="+_address2+"&comment="+_comment+"&product_id=" + <?php echo $product_id; ?>+"&product_q="+_pq+"&products_name=<?php echo $product_name; ?>&isTaksa=<?php echo $isTaksa; ?>&price_input="+priceall+"&pogasitelni_vnoski_input="+_pogasitelni_vnoski_input+"&zastrahovka_input="+_zastrahovka_input+"&parva_input="+_parva_input+"&mesecna_vnoska_input="+_mesecna_vnoska_input+"&gpr_input="+_gpr_input+"&tbireklama="+_tbireklama+"&tbi_mod_version=<?php echo $tbi_mod_version; ?>", true);
                    xmlhttp.send();
                }
            }
            function close_tbi_box(){
                document.getElementById("tbi_body").innerHTML = "";
                document.getElementById('tbi_box').style.display = "none";
            }
            function change_btn_tbicredit(){
                var uslovia = document.getElementById('uslovia');
                var buy_tbicredit = document.getElementById('buy_tbicredit');
                if (uslovia.checked){
                    buy_tbicredit.disabled = false;
                }else{
                    buy_tbicredit.disabled = true;
                }
            }
            function preizcisli_tbi(select_vnoski, select_zastrahovka, input_parva){
                showTbiBoxHtml(cid, select_vnoski.options[select_vnoski.selectedIndex].value, select_zastrahovka.options[select_zastrahovka.selectedIndex].value, input_parva.value);
            }
            function isNumberKey(evt){
                var charCode = (evt.which) ? evt.which : event.keyCode
                if (charCode > 31 && (charCode < 48 || charCode > 57)){
                    return false;
                }
                return true;
            }
            function ispogoliamo(_price, _parva){
                if ((parseInt(_price.value) - 100) < parseInt(_parva.value)){
                    _parva.value = _parva.value.slice(0,-1);
                    return false;
                }
                return true;
            }
            function cyrKey(_e){
                _e.value = _e.value.replace(/[a-zA-Z]*/, "");
            }
            function elementOnFocus(_e){
                _e.style.border="3px solid #e55a00";
            }
            function checkForm(_name, _egn, _phone, _email, _address, _address2, _comment, _pogasitelni_vnoski_input, _zastrahovka_input, _parva_input, _mesecna_vnoska_input, _gpr_input, _tbireklama) {
                var _test = true;
                if(_name.value == '') {
                    _name.style.border="3px solid red";
                    _test = false;
                }else{
                    var re = /^[\w ]+$/;
                    if(!re.test(_egn.value)) {
                        _egn.style.border="3px solid red";
                        _test = false;
                    }else{
                        if(_phone.value == '') {
                            _phone.style.border="3px solid red";
                            _test = false;
                        }else{
                            if(_email.value == '') {
                                _email.style.border="3px solid red";
                                _test = false;
                            }else{
                                if(_address.value == '') {
                                    _address.style.border="3px solid red";
                                    _test = false;
                                }else{
                                    if(_address2.value == '') {
                                        _address2.style.border="3px solid red";
                                        _test = false;
                                    }
                                }
                            }
                        }
                    }
                }
                if (_test){
                    tbisend(_name.value, _egn.value, _phone.value, _email.value, _address.value, _address2.value, _comment.value, _pogasitelni_vnoski_input, _zastrahovka_input, _parva_input, _mesecna_vnoska_input, _gpr_input, _tbireklama);
                }
            }
        </script>
        <style>
            *{padding:0;margin:0;}

            /* tbi float */
            .tbi-label-container{
                z-index:999;
                position:fixed;
                top:calc(100% / 2 - 130px);
                left:67px;
                display:table;
                visibility: hidden;
            }
            .tbi-label-text{
                width:410px;
                height:260px;
                color:#696969;
                background:#f5f5f5;
                display:table-cell;
                vertical-align:top;
                padding-left:5px;
                border:1px solid #f18900;
                border-radius:3px;
            }
            .tbi-label-text-a{
                text-align:center;
            }
            .tbi-label-text-a a{
                color:#b73607;
            }
            .tbi-label-text-a a:hover{
                color:#672207;
                text-decoration:underline;
            }
            .tbi-label-arrow{
                display:table-cell;
                vertical-align:middle;
                color:#f5f5f5;
                opacity:1;
            }
            .tbi_float{
                z-index:999;
                position:fixed;
                width:60px;
                height:60px;
                top:calc(100% / 2 - 30px);
                left:0px;
                background-color:#ffffff;
                border-top:1px solid #f18900;
                border-right:1px solid #f18900;
                border-bottom:1px solid #f18900;
                color:#FFF;
                border-top-right-radius:8px;
                border-bottom-right-radius:8px;
                text-align:center;
                box-shadow: 2px 2px 3px #999;
                cursor:pointer;
            }
            .tbi-my-float{
                margin-top:12px;
            }
        </style>
        <?php
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, 'https://tbibank.support/function/getparameters.php?cid='.$unicid);
        $paramstbi=json_decode(curl_exec($ch), true);
        curl_close($ch);

        //detect mobile
        $useragent = $_SERVER['HTTP_USER_AGENT'];
        if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4))){
            $testmobile = "Yes";
        }else{
            $testmobile = "No";
        }
        //test 0%
        if ($paramstbi['tbi_purcent_default'] == 2 || $paramstbi['tbi_purcent_default'] == 3 || $paramstbi['tbi_purcent_default'] == 4){
            $tbi_picture = 'https://tbibank.support/calculators/assets/img/tbim10.png';
        }else{
            $tbi_picture = 'https://tbibank.support/calculators/assets/img/tbim' . $paramstbi['tbi_container_reklama'] . '.png';
        }

        ?>
        <?php if ($paramstbi['tbi_container_status'] == 'Yes'){ ?>
            <div class="tbi_float" onclick="<?php if ($testmobile = 'No'){ echo 'tbiChangeContainer();'; } ?>">
                <img src="https://tbibank.support/dist/img/tbi_logo.png" class="tbi-my-float">
            </div>
            <div class="tbi-label-container">
                <i class="fa fa-play fa-rotate-180 tbi-label-arrow"></i>
                <div class="tbi-label-text">
                    <div style="padding-bottom:5px;"></div>
                    <img src="<?php echo $tbi_picture; ?>">
                    <div style="font-size:16px;padding-top:3px;"><?php echo $paramstbi['tbi_container_txt1']; ?></div>
                    <p style="font-size:14px;"><?php echo $paramstbi['tbi_container_txt2']; ?></p>
                    <div class="tbi-label-text-a"><a href="https://tbibank.support/procedure.php" target="_blank" alt="ИНФОРМАЦИЯ ЗА ОНЛАЙН ПАЗАРУВАНЕ НА КРЕДИТ С TBI BANK">ИНФОРМАЦИЯ ЗА ОНЛАЙН ПАЗАРУВАНЕ НА КРЕДИТ!</a></div>
                </div>
            </div>
            <script type="application/javascript">
                function tbiChangeContainer(){
                    var tbi_label_container = document.getElementsByClassName("tbi-label-container")[0];
                    if (tbi_label_container.style.visibility == 'visible'){
                        tbi_label_container.style.visibility = 'hidden';
                        tbi_label_container.style.opacity = 0;
                        tbi_label_container.style.transition = 'visibility 0s, opacity 0.5s ease';
                    }else{
                        tbi_label_container.style.visibility = 'visible';
                        tbi_label_container.style.opacity = 1;
                    }
                }
            </script>
        <?php } ?>
<?php }
    /* Край на PHP кода за Кредитен Калкулатор TBI Bank */
    }//check json
?>
