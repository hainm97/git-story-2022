<?php
if (!defined('SERVER_ROOT')) {
    exit('No direct script access allowed');
}
use Nth\Helper\Characters;
$input = new Nth\Filter\Input();
$this->template->title = $ttDetail->TEN_THU_TUC;
$this->template->display('bocongan/frontend.optimize-header.php');

// CHECK LOGIN QUA TÀI KHOẢN CÔNG DÂN BCA (KHÔNG PHẢI CÔNG DÂN IGATE)
$xnc_title_login_arr = getThamSoArray(Model\Entity\System\Parameter::fromId('BCA_REDIRECT_TITLE_FORM_LOGIN')->getValue());
$xnc_title_login = !empty($xnc_title_login_arr['TITLE']) ? $xnc_title_login_arr['TITLE'] : 'Đăng nhập tài khoản Bộ Công An';
$xnc_note_small = !empty($xnc_title_login_arr['NOTE_FORM_SMALL']) ? $xnc_title_login_arr['NOTE_FORM_SMALL'] : '';
$xnc_link_dky_tk = Model\Entity\System\Parameter::fromId('BCA_REDIRECT_LINK_DKY_TK_CONGDAN')->getValue();

$xnc_thutuc_require_login = Model\Entity\System\Parameter::fromId('BCA_THU_TUC_YC_LOGIN_REDIRECT_BCA')->getValue();
$require_tt = $xnc_thutuc_require_login ? explode(',', $xnc_thutuc_require_login) : [];
$CHECK_LOGIN_TK_BCA = Session::get(TIEP_DAU_NGU_SESSION.'BCA_REDIRECT_CHECK_LOGINED');
// END CHECK LOGIN

// Redirect sang module bocongan / tiepnhanonline
// $tsqt_nop_hs_2b = Model\Entity\System\Parameter::fromId('BCA_CAU_HINH_TT_QUY_TRINH_NOP_HS_HAI_BUOC')->getValue();
// $tsqt_nop_hs_3b = Model\Entity\System\Parameter::fromId('BCA_CAU_HINH_TT_QUY_TRINH_NOP_HS_BA_BUOC')->getValue();
// $qt_nop_hs_2b = $tsqt_nop_hs_2b ? explode(',', $tsqt_nop_hs_2b) : [];
// $qt_nop_hs_3b = $tsqt_nop_hs_3b ? explode(',', $tsqt_nop_hs_3b) : [];
// END CHECK LOGIN

echo $this->hidden('controller', $this->get_controller_url());

$MA_THU_TUC = $ttDetail->MA_THU_TUC;

// Check thủ tục có cấu hình theo quy trình nộp hồ sơ của BCA không
// if (in_array($MA_THU_TUC, $qt_nop_hs_2b) || in_array($MA_THU_TUC, $qt_nop_hs_3b) ) {
    $doLink = SITE_ROOT . 'bo-cong-an/tiep-nhan-online/chon-truong-hop-ho-so?ma-thu-tuc-public=' . $MA_THU_TUC;
// } else {
//     $doLink = SITE_ROOT . 'dich-vu-cong/tiep-nhan-online/chon-truong-hop-ho-so?ma-thu-tuc-public=' . $MA_THU_TUC;
// }

?>
<style>
    .tthc-page-detail .tthc-step .tthc-list-item .tthc-list-item-detail p {
        font-size: 14px !important;
    }
    .cus-header-tthcview {
        color: rgb(31, 118, 67);
        font-size: 15px !important;
        font-weight: 600;
    }
    body#cke_pastebin {
        all: unset !important;
    }
    .tthc-list-item-detail ul {
        text-align: justify !important;
    }
</style>
<div class="main-wrapper dvc-main-wrap">
    <div class="main-content">
        <div class="bca-breadcrumb">
            <div class="container dvc-container">
                <ol class="breadcrumb">
                    <li><a href="<?php echo SITE_ROOT ?>bocongan/home">Trang chủ</a></li>
                    <li class="active"><?php echo $ttDetail->TEN_THU_TUC?></li>
                </ol>
            </div>
        </div>
        <div class="linhvuc-main">
            <div class="container dvc-container">
                <div class="col-md-3 col-md-push-9 col-sm-4 col-sm-push-8 col-xs-12 linhvuc-main-right">
                    <?php $this->block->view('bca_searchlinhvuc'); ?>
                </div>
                <div class="col-md-9 col-md-pull-3 col-sm-8 col-sm-pull-4 col-xs-12 linhvuc-main-left">
                    <div class="tthc-title">
                        <h4><?php echo $ttDetail->TEN_THU_TUC?></h4>
                        <?php if($ttDetail->MA_MUC_DO == 'MUC_DO_3' || $ttDetail->MA_MUC_DO == 'MUC_DO_4') :?>
                        <div class="tthc-title-bottom">
                            <?php
                            if(!in_array($MA_THU_TUC, $require_tt)) { ?>
                                <a href="<?php echo $doLink?>" class="btn bca-search-btn">Nộp hồ sơ</a>
                            <?php } else {
                                if ($CHECK_LOGIN_TK_BCA) { ?>
                                    <a href="<?php echo $doLink ?>" class="btn bca-search-btn">Nộp hồ sơ</a>
                                <?php } else { ?>
                                    <a href="#" data-toggle="modal" data-target="#require-login-bca"
                                       class="btn bca-search-btn">Nộp hồ sơ</a>
                                <?php }
                            }
                            ?>
                        </div>
                        <?php endif;?>
                        <div class="" style="clear:both"></div>

                        <!-- require-login-bca -->
                        <div class="modal fade" id="require-login-bca" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header" style="padding: 10px 15px;text-align: center;background: #c3242a">
                                        <h3 style="font-size: 21px;color: #fff;" class="modal-title" id="exampleModalLabel">
                                            <?php echo $xnc_title_login; ?> </h3>
                                    </div>
                                    <div class="modal-body">
                                        <div class="form-group">
                                            <label for="inputUsername">Tên đăng nhập</label>
                                            <input type="text" name="P_USERNAME" class="form-control" id="inputUsername" placeholder="Nhập tên đăng nhập">
                                        </div>
                                        <div class="form-group">
                                            <label for="inputPassword">Mật khẩu</label>
                                            <input type="password" NAME="P_MAT_KHAU" class="form-control" id="inputPassword" placeholder="Nhập mật khẩu">
                                        </div>
                                        <div class="form-group">
                                            <label for="inputPassword">Tỉnh thành</label>
                                            <select class="form-control" name="P_TEN_CSLT" id="inputCSLT">
                                                <?php echo $option_dmTT; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <?php echo $xnc_note_small; ?>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <a style="text-decoration: underline;font-size: 16px;float: left;color: #0e1de4;" target="_blank" href="<?php echo $xnc_link_dky_tk ? $xnc_link_dky_tk : '#'; ?>">Đăng ký tài khoản</a>
                                        <span class="btn btn-default" data-dismiss="modal">Đóng</span>
                                        <span class="btn btn-primary" onclick="connectLoginBCA()">Đăng nhập</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tthc-accordion">
                        <div class="tthc-page-detail">
                            <div class="tthc-step" id="accordion2" role="tablist" aria-multiselectable="true">
                                <div class="tthc-list-item">
                                    <div role="button" class="item-title" data-toggle="collapse"
                                       href="#collapse1a" aria-expanded="false" aria-controls="collapse1a">Lĩnh vực<i class="fa fa-angle-right"></i>
                                    </div>
                                    <div id="collapse1a" class="tthc-panel-collapse in collapse" role="tabpanel">
                                        <div class="tthc-list-item-detail">
                                            <?php echo $ttDetail->TEN_LINH_VUC_THU_TUC; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="tthc-list-item">
                                    <div role="button" class="item-title" data-toggle="collapse"
                                         href="#collapse2a" aria-expanded="false" aria-controls="collapse2a">Cơ quan thực hiện<i class="fa fa-angle-right"></i>
                                    </div>
                                    <div id="collapse2a" class="tthc-panel-collapse in collapse" role="tabpanel">
                                        <div class="tthc-list-item-detail">
                                            <?php
                                            $coQuanTH = Characters::fromClob($ttDetail->CO_QUAN_THUC_HIEN);
                                            echo $input->revert($coQuanTH);
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="tthc-list-item">
                                    <div role="button" class="item-title" data-toggle="collapse"
                                         href="#collapse3a" aria-expanded="false" aria-controls="collapse3a">Cách thức thực hiện<i class="fa fa-angle-right"></i>
                                    </div>
                                    <div id="collapse3a" class="tthc-panel-collapse in collapse" role="tabpanel">
                                        <div class="tthc-list-item-detail">
                                            <?php
                                            $cachThucTH = Characters::fromClob($ttDetail->CACH_THUC_THUC_HIEN);
                                            echo $input->revert($cachThucTH);
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="tthc-list-item">
                                    <div role="button" class="item-title" data-toggle="collapse"
                                         href="#collapse4a" aria-expanded="false" aria-controls="collapse4a">Trình tự thực hiện<i class="fa fa-angle-right"></i>
                                    </div>
                                    <div id="collapse4a" class="tthc-panel-collapse in collapse" role="tabpanel">
                                        <div class="tthc-list-item-detail">
                                            <?php
                                            $TrinhTu = Characters::fromClob($ttDetail->TRINH_TU_THUC_HIEN);
                                            echo $input->revert($TrinhTu);
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="tthc-list-item">
                                    <div role="button" class="item-title collapsed" data-toggle="collapse"
                                         href="#collapse5a" aria-expanded="false" aria-controls="collapse5a">Thời hạn giải quyết<i class="fa fa-angle-right"></i>
                                    </div>
                                    <div id="collapse5a" class="tthc-panel-collapse collapse" role="tabpanel">
                                        <div class="tthc-list-item-detail">
                                            <?php
                                            $thoiHan = Characters::fromClob($ttDetail->THOI_HAN_GIAI_QUYET);
                                            echo $input->revert($thoiHan);
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="tthc-list-item">
                                    <div role="button" class="item-title collapsed" data-toggle="collapse"
                                         href="#collapse11a" aria-expanded="false" aria-controls="collapse11a">Phí<i class="fa fa-angle-right"></i>
                                    </div>
                                    <div id="collapse11a" class="tthc-panel-collapse collapse" role="tabpanel">
                                        <div class="tthc-list-item-detail">
                                            <?php
                                            $Phi = Characters::fromClob($ttDetail->PHI);
                                            echo $input->revert($Phi);
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="tthc-list-item">
                                    <div role="button" class="item-title collapsed" data-toggle="collapse"
                                         href="#collapse12a" aria-expanded="false" aria-controls="collapse12a">Lệ Phí<i class="fa fa-angle-right"></i>
                                    </div>
                                    <div id="collapse12a" class="tthc-panel-collapse collapse" role="tabpanel">
                                        <div class="tthc-list-item-detail">
                                            <?php
                                            $lePhi = Characters::fromClob($ttDetail->LE_PHI);
                                            echo $input->revert($lePhi);
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="tthc-list-item">
                                    <div role="button" class="item-title collapsed" data-toggle="collapse"
                                         href="#collapse6a" aria-expanded="false" aria-controls="collapse6a">Thành phần hồ sơ<i class="fa fa-angle-right"></i>
                                    </div>
                                    <div id="collapse6a" class="tthc-panel-collapse collapse" role="tabpanel">
                                        <div class="tthc-list-item-detail">
                                            <?php
                                            $TPHS = Characters::fromClob($ttDetail->THANH_PHAN_HO_SO);
                                            echo $input->revert($TPHS);
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="tthc-list-item">
                                    <div role="button" class="item-title collapsed" data-toggle="collapse"
                                       href="#collapse7a" aria-expanded="false" aria-controls="collapse7a">Yêu cầu - điều kiện<i class="fa fa-angle-right"></i>
                                    </div>
                                    <div id="collapse7a" class="tthc-panel-collapse collapse" role="tabpanel">
                                        <div class="tthc-list-item-detail">
                                            <?php
                                            $yeuCauDk = Characters::fromClob($ttDetail->YEU_CAU_DIEU_KIEN);
                                            echo $input->revert($yeuCauDk);
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="tthc-list-item">
                                    <div role="button" class="item-title collapsed" data-toggle="collapse"
                                       href="#collapse8a" aria-expanded="false" aria-controls="collapse8a">Căn cứ pháp lý<i class="fa fa-angle-right"></i>
                                    </div>
                                    <div id="collapse8a" class="tthc-panel-collapse collapse" role="tabpanel">
                                        <div class="tthc-list-item-detail">
                                            <?php
                                            $canCu = Characters::fromClob($ttDetail->CAN_CU_PHAP_LY);
                                            echo $input->revert($canCu);
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="tthc-list-item">
                                    <div role="button" class="item-title collapsed" data-toggle="collapse"
                                       href="#collapse9a" aria-expanded="false" aria-controls="collapse9a">Biểu mẫu<i class="fa fa-angle-right"></i>
                                    </div>
                                    <div id="collapse9a" class="tthc-panel-collapse collapse" role="tabpanel">
                                        <div class="tthc-list-item-detail">
                                            <?php if ($ttDetail->FILE_MAU != '') : ?>
                                                <p>
                                                    <?php echo (Characters::fromClob($ttDetail->THANH_PHAN_HO_SO)); ?>
                                                </p>
                                                <p>
                                                    <strong><i class="fa fa-paperclip"></i> File mẫu: </strong>
                                                    <?php echo str_replace('SITE_ROOT', SITE_ROOT, Characters::fromClob($ttDetail->FILE_MAU)); ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="tthc-list-item">
                                    <div role="button" class="item-title collapsed" data-toggle="collapse"
                                       href="#collapse10a" aria-expanded="false" aria-controls="collapse10a">Kết quả thực hiện<i class="fa fa-angle-right"></i>
                                    </div>
                                    <div id="collapse10a" class="tthc-panel-collapse collapse" role="tabpanel">
                                        <div class="tthc-list-item-detail">
                                            <?php
                                            $ketQua = Characters::fromClob($ttDetail->KET_QUA_THUC_HIEN);
                                            echo $input->revert($ketQua);
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$this->template->display('bocongan/frontend.optimize-footer.php');
?>
<script>
    require(['jquery'],function(){
        $('.lv-title-icon').css("height",$('.lv-title-text').height());
    });

    function connectLoginBCA() {
        $.ajax({
            type: 'POST',
            url: SITE_ROOT + 'bo-cong-an/tiep-nhan-online/checkLoginTKBCA',
            data: {
                username: $('input[name=P_USERNAME]').val(),
                password: $('input[name=P_MAT_KHAU]').val(),
                inputCSLT: $('select[name=P_TEN_CSLT]').val(),
                macoquan: '<?php echo $ttDetail->MA_CO_QUAN ?>'
            },
            success: function (r) {
                var result = JSON.parse(r);
                if(result.code != 0) {
                    alert(result.message);
                    return false;
                } else {
                    window.open('<?php echo $doLink ? $doLink : '#'; ?>', "_self");
                }
            },
            error: function(r) {
                alert("Xảy ra lỗi! Vui lòng thử lại.");
            }
        });
    }
</script>