<?php
if (!defined('SERVER_ROOT')) {
    exit('No direct script access allowed');
}
use Model\Entity;
$this->template->title = $progress->getActiveStep()->getDescription();
// Hienctt KV1: check riêng header / footer cho BCA
$BCA_SERVICE_ACTIVE = Model\Entity\System\Parameter::fromId('BCA_SERVICE_ACTIVE', ['cache' => false])->getValue();
$HNI_TEMPLATE_ACTIVE = Model\Entity\System\Parameter::fromId('HNI_TEMPLATE_ACTIVE')->getValue();
$isAGTemplate = Entity\System\Parameter::fromId('MA_TINH_THANH')->getValue() == '89';
$isVPC = Entity\System\Parameter::fromId('MA_TINH_THANH')->getValue() == '26';
// Hunglv.ybi IGATESUPP-12290
$YBI_BCCI = Entity\System\Parameter::fromId('YBI_BCCI')->getValue();
$VNPOST_NHAN_HS_ONLINE = Entity\System\Parameter::fromId('VNPOST_NHAN_HS_ONLINE')->getValue();
$BCA_TINH_PHI_TT_NEW = Model\Entity\System\Parameter::fromId('BCA_TINH_PHI_TT_NEW')->getValue();
if ($isAGTemplate) {
    $this->template->display('angiang/frontend.header.php');
} else if($BCA_SERVICE_ACTIVE == 1) {
    $this->template->display('bocongan/frontend.optimize-header.php');
} else if($HNI_TEMPLATE_ACTIVE){
    $this->template->display('hanoi/frontend.header.php');
} else if ($isVPC){
    
    $this->template->display('vinhphuc/frontend.optimize-header.php');
}
else{
    $this->template->display('dichvucong/frontend.optimize-header.php');
}

$csrf = new Zend\Validator\Csrf();
$token = $csrf->getHash();

$maLoaiCuocPhatTra =  \Model\Entity\System\Parameter::fromId('MA_LOAI_LE_PHI_CUOC_PHAT_TRA')->getvalue();
$batBuocUploadFileThanhToan =  \Model\Entity\System\Parameter::fromId('TN_ONL_BAT_BUOC_FILE_THANH_TOAN_HTTT2')->getvalue();
$taiKhoanCongDan = new \Model\CongDan();
$duLieuCongDan = $taiKhoanCongDan->getSessionData();
$laTaiKhoanDoanhNghiep = 0;
if (count($duLieuCongDan) > 0) {
    $laTaiKhoanDoanhNghiep = !empty($duLieuCongDan['P_LA_DOANH_NGHIEP']) ? $duLieuCongDan['P_LA_DOANH_NGHIEP'] : 0;
}
$trangThaiTaiKhoanNganHang = 0;
if ($laTaiKhoanDoanhNghiep == 1) {
    $taiKhoanNganHang = $this->model->SELECT_TAI_KHOAN_NGAN_HANG($duLieuCongDan['P_MA_CONG_DAN']);
    $trangThaiTaiKhoanNganHang = !empty($taiKhoanNganHang['STATUS']) ? $taiKhoanNganHang['STATUS'] : 0;
}

$labelThuGomHoSo = Model\Entity\System\Parameter::fromId('LABEL_THU_GOM_HO_SO')->getValue();
$lb_lephi =  Entity\System\Parameter::fromId('LABEL_LE_PHI_TIEP_NHAN')->getValue();

$CHON_HINH_THUC = Model\Entity\System\Parameter::fromId('CHON_HINH_THUC_NOP_NHAN_HO_SO')->getValue();
$NAME_STEP_DVC_NOP_HS_ONLINE = Model\Entity\System\Parameter::fromId('NAME_STEP_DVC_NOP_HS_ONLINE')->getValue();
    foreach ($CHON_HINH_THUC as $arr) {
        foreach ($arr as $key => $value) {
            $CHON_HINH_THUC[$key] = $value;
        }
    }
if ($labelThuGomHoSo == '' || empty($labelThuGomHoSo)) {
    $labelThuGomHoSo = 'Cước thu gom hồ sơ';
}
$BDH_AN_THANH_TOAN_TRUC_TUYEN = Model\Entity\System\Parameter::fromId('BDH_AN_THANH_TOAN_TRUC_TUYEN')->getValue();
?>
<style type="text/css">
    section .amount{text-align: right}
    section .amount.total{font-weight: bold;}
    .label-fill-out, .label-fill-out-sm{font-size: 11px;position: relative;top:-8px;margin-right: 5px;}
    .label-fill-out-sm{font-size: 9px;top:-5px;margin-right: 2px;}
    #chi-phi-phai-nop-wrapper table .unit{text-align: left}
    #chi-phi-phai-nop-wrapper table .desc{text-align: left;font-style: italic}

    section .amount{text-align: right}
    section .amount.total{font-weight: bold;}
    section .paid .name, section .paid .amount, section .paid .number, section .paid .require, section .paid .unit{text-decoration: line-through}
    section .paid .desc{color:#13a20a}
</style>
<script type="text/javascript">
    var __current = { page: {}, fs: [], hiddenData: {} }
    define('NthLib', [
        'Nth/Nth',
        'Nth/Alert',
        'Nth/Confirm',
        'Nth/List',
        'Nth/Helper/Number',
        'Nth/FormBuilder',
        'Nth/FormBuilder/Convertor',
        'Nth/FormBuilder/Fieldset'
    ], function (Nth) {
        Nth.FormBuilder.__getInstance().setOption('siteRoot', SITE_ROOT);
        return Nth;
    });
    define('queryData', function () {
        return JSON.parse('<?php echo addslashes(json_encode($queryData)) ?>');
    });
    define('donViTiepNhan', function () {
        return JSON.parse('<?php echo addslashes(json_encode($donViTiepNhan->toArray())) ?>');
    });
    define('hoSoOnline', function () {
        return JSON.parse('<?php echo addslashes(json_encode($hoSoOnline->toArray())) ?>');
    });
    require(['NthLib', 'queryData', 'donViTiepNhan', 'hoSoOnline'], function (Nth, queryData, donViTiepNhan, hoSoOnline) {
        __current.hiddenData = queryData;
        __current.page.onLoad = function () {
            $('#btn-next').attr('disabled', true);
        }
        __current.page.onReady = function () {
            $('#btn-next').removeAttr('disabled');
        }
        __current.getFieldset = function (name) {
            var rs = this.fs.filter(function (a) {
                return a.getName() === name;
            });
            return rs.length === 1 ? rs[0] : null;
        }
        __current.layTongLePhi = function (options) {
            options = $.extend({}, options);
            var total = 0;
            $.each(hoSoOnline.dmLePhiHoSo, function () {
                if ($.isNumeric(options.thanhToanCho)) {
                    if (parseInt(this.thanhToanCho) === options.thanhToanCho) {
                        total += (parseInt(this.mucLePhi) *  Math.abs(parseInt(this.soLuong)));
                    }
                } else {
                    total += (parseInt(this.mucLePhi) *  Math.abs(parseInt(this.soLuong)));
                }
            });
            return total;
        }
        __current.layTongLePhiPhaiThanhToan = function (options) {
            options = $.extend({}, options);
            var total = 0;
            $.each(hoSoOnline.dmLePhiHoSo, function () {
                if ($.isNumeric(options.thanhToanCho)) {
                    if (parseInt(this.thanhToanCho) === options.thanhToanCho && this.batBuocThanhToan == 1) {
                        total += (parseInt(this.mucLePhi) *  Math.abs(parseInt(this.soLuong)));
                    }
                } else {
                    total += (parseInt(this.mucLePhi) *  Math.abs(parseInt(this.soLuong)));
                }
            });
            return total;
        }
        __current.editLePhiHoSo = function (lePhiHoSo, desc, refreshDom) {
            if (lePhiHoSo.soLuong === undefined || lePhiHoSo.soLuong == null || lePhiHoSo.soLuong.length < 0) {
                lePhiHoSo.soLuong = 1;
            }
            var items = hoSoOnline.dmLePhiHoSo.filter(function (a) {
                return parseInt(a.maLoaiLePhi) === parseInt(lePhiHoSo.maLoaiLePhi);
            });
            if (items.length === 1) {
                items[0].maHoSoOnline = lePhiHoSo.maHoSoOnline;
                items[0].mucLePhi = lePhiHoSo.mucLePhi;
                items[0].thanhToanCho = lePhiHoSo.thanhToanCho;
                items[0].maLePhiThuTuc = lePhiHoSo.maLePhiThuTuc;
                items[0].maCuocVanChuyen = lePhiHoSo.maCuocVanChuyen;
                items[0].soLuong =  Math.abs(lePhiHoSo.soLuong);
                items[0].batBuocThanhToan =  lePhiHoSo.batBuocThanhToan;
            } else {
                hoSoOnline.dmLePhiHoSo.push(lePhiHoSo);
            }
            if (refreshDom) {
                $('#loai-le-phi-' + lePhiHoSo.maLoaiLePhi).remove();
                var $tr = $('<tr/>', {id: 'loai-le-phi-' + lePhiHoSo.maLoaiLePhi}).appendTo($('#tbody-' + lePhiHoSo.thanhToanCho));
                $('<td/>', {class: 'name'}).html(lePhiHoSo.loaiLePhi.tenLoai).appendTo($tr);
                $('<td/>', {class: 'number'}).html(lePhiHoSo.soLuong).appendTo($tr);
                $('<td/>', {class: 'amount'}).html(new Nth.Helper.Number(lePhiHoSo.mucLePhi).addCommas().getNumber()).appendTo($tr);
                $('<td/>', {class: 'unit'}).html('<strong>VNĐ</strong>').appendTo($tr);
                $('<td/>', {class: 'require'}).html((lePhiHoSo.batBuocThanhToan == 1 ? '<strong>Có</strong>' : '<strong>Không</strong>')).appendTo($tr);
                $('<td/>', {class: 'desc'}).html(desc).appendTo($tr);
            }
            this.hienThiTongLePhi();
        }
        __current.removeLePhiHoSo = function (options, includeDom) {
            options = $.extend({}, options);
            $.each(hoSoOnline.dmLePhiHoSo, function (i) {
                if (parseInt(options.maLoaiLePhi) === parseInt(this.maLoaiLePhi) && (this.maLePhi == null || this.maLePhi == undefined)) {
                    hoSoOnline.dmLePhiHoSo.splice(i, 1);
                    return false;
                }
            });
            if (includeDom) {
                $('#loai-le-phi-' + options.maLoaiLePhi).remove();
            }
            this.hienThiTongLePhi();
        }
        __current.hienThiTongLePhi = function () {
            $('tr#tong-cong-1 .amount, tr#tong-cong-2 .amount, tr#tong-le-phi-bat-buoc .amount').html('0');
            $('tr#tong-cong-1 .desc, tr#tong-cong-2 .desc, tr#tong-le-phi-bat-buoc .desc').html('<i class="fa fa-spin fa-spinner"></i> Đang tính...');
            if (this.timer) {
                clearTimeout(this.timer);
            }
            var inst = this;
            this.timer = setTimeout(function () {
                var total1 = inst.layTongLePhi({thanhToanCho: 1});
                var total2 = inst.layTongLePhi({thanhToanCho: 2});
                var tongLePhiBatBuoc = inst.layTongLePhiPhaiThanhToan({thanhToanCho: 1});
                 //Lâm thực hiện 30/08/2019
                $('span#tongLePhi').html(new Nth.Helper.Number(tongLePhiBatBuoc).addCommas().getNumber());
                $('tr#tong-cong-1 .amount').html(new Nth.Helper.Number(total1).addCommas().getNumber());
                $('tr#tong-cong-1 .desc').html(total1 > 0 ? '<strong>Thanh toán cho cơ quan giải quyết</strong>' : null);
                $('tr#tong-cong-2 .amount').html(new Nth.Helper.Number(total2).addCommas().getNumber());
                $('tr#tong-cong-2 .desc').html(total2 > 0 ? '<strong>Thanh toán trực tiếp cho bưu điện</strong>' : null);
                $('tr#tong-le-phi-bat-buoc .amount').html(new Nth.Helper.Number(tongLePhiBatBuoc).addCommas().getNumber());
                $('tr#tong-le-phi-bat-buoc .desc').html(tongLePhiBatBuoc > 0 ? '<strong> <?php echo ucfirst(strtolower($lb_lephi));?> buộc phải thanh toán trước</strong>' : null);
                $('section#le-phi-nop-2-wrapper')[total2 > 0 ? 'show' : 'hide']();
                var fsPttt = inst.getFieldset('fs-phuong-thuc-thanh-toan');
                if (fsPttt) {
                    fsPttt.getOption('onHienThiTongLePhi').call(fsPttt, total1);
                }
                <?php if((int) Entity\System\Parameter::fromId('DOI_TUONG_GIAM_CUOC_VNPOST')->getValue() === 1): ?> //IGATESUPP-26470 tttruong-kv1
                    $('#chonDoiTuong').prop('selectedIndex',0);
                    $('#tongTien').html(new Nth.Helper.Number(total2).addCommas().getNumber() + ' VNĐ');
                    $('#txt_tienDoiTuongGiam').val(total2);
                <?php endif;?>
            }, 1000);
        }
        __current.setCuocPhiThuGom = function (data, desc) {
            this.message = null;
            var item = $.extend({}, data, {
                maHoSoOnline: hoSoOnline.maHoSo, 
                maLoaiLePhi: 2,
                loaiLePhi: {maLoai: 2, tenLoai: '<?php echo $labelThuGomHoSo; ?>'}
            });
            if (data === false) {
                return this.removeLePhiHoSo(item, true);
            } else if (item.mucLePhi === null) {
                this.message = desc;
                this.removeLePhiHoSo(item, true);
                return Nth.Alert(desc);
            }
            this.editLePhiHoSo(item, desc, true);
            this.hienThiTongLePhi();
        }
        __current.setCuocPhiPhatTra = function (data, desc) {
            this.message = null;
            var cuocPhatTra = parseInt('<?php  echo $maLoaiCuocPhatTra ? $maLoaiCuocPhatTra : 3 ?>');
            var item = $.extend({}, data, {
                maHoSoOnline: hoSoOnline.maHoSo, 
                maLoaiLePhi: cuocPhatTra,
                loaiLePhi: {maLoai: cuocPhatTra, tenLoai: 'Cước phát trả kết quả hồ sơ'}
            });
            if (data === false) {
                return this.removeLePhiHoSo(item, true);
            } else if (item.mucLePhi === null) {
                this.message = desc;
                this.removeLePhiHoSo(item, true);
                return Nth.Alert(desc);
            }
            this.editLePhiHoSo(item, desc, true);
            this.hienThiTongLePhi();
        }
        __current.tinhCuocPhiThuGom = function () {
            var fs = this.getFieldset('fs-hinh-thuc-nop');
            var maHinhThucNop = fs ? fs.getComponent('maHinhThucNop') : null;
            if (maHinhThucNop && parseInt(maHinhThucNop.getValue()) === 1) {
                var maPhuongXaThuGom = fs.getComponent('maPhuongXaThuGom').getValue();
                if (maPhuongXaThuGom) {
                    this.page.onLoad.call();
                    this.setCuocPhiThuGom({mucLePhi: 0}, '<i class="fa fa-spin fa-spinner"></i> Đang tính...');
                    var inst = this;
                    var fsPttt = this.getFieldset('fs-phuong-thuc-thanh-toan');
                    return $.ajax(SITE_ROOT + 'trang-chu/dich-vu-cong/ajaxTinhCuocThuGomHoSo', {
                        type: "POST",
                        data: {
                            maThuTuc: hoSoOnline.qttt.thuTuc.maThuTuc,
                            maCoQuan: hoSoOnline.qttt.thuTuc.maCoQuan,
                            maPhuongXaNguoiGui: maPhuongXaThuGom,
                            maPhuongXaNguoiNhan: donViTiepNhan.maPhuongXaLv,
                            phiThuHo: fsPttt && parseInt(fsPttt.getComponent('maHinhThucThanhToan').getValue()) === 6 ? this.layTongLePhi({thanhToanCho: 1}) : null
                        },
                        dataType: 'json',
                        success: function (r) {
                            inst.setCuocPhiThuGom(r.data, r.desc);
                            inst.page.onReady.call();
                        }
                    });
                }
            }
            return this.setCuocPhiThuGom(false);
        }
        __current.tinhCuocPhiPhatTra = function () {
            var fs = this.getFieldset('fs-noi-nhan-ket-qua');
            if (fs && parseInt(fs.getComponent('maHinhThucNhanKetQua').getValue()) === 1) {
                var maPhuongXaNhanKetQua = fs.getComponent('maPhuongXaNhanKetQua').getValue();
                if (maPhuongXaNhanKetQua) {
                    this.page.onLoad.call();
                    this.setCuocPhiPhatTra({mucLePhi: 0, thanhToanCho: 2}, '<i class="fa fa-spin fa-spinner"></i> Đang tính...');
                    var inst = this;
                    return $.ajax(SITE_ROOT + 'trang-chu/dich-vu-cong/ajaxTinhCuocPhatTraHoSo', {
                        type: "POST",
                        data: {
                            maThuTuc: hoSoOnline.qttt.thuTuc.maThuTuc,
                            maCoQuan: hoSoOnline.qttt.thuTuc.maCoQuan,
                            maPhuongXaNguoiGui: donViTiepNhan.maPhuongXaLv,
                            maPhuongXaNguoiNhan: maPhuongXaNhanKetQua
                        },
                        dataType: 'json',
                        success: function (r) {
                            inst.setCuocPhiPhatTra(r.data, r.desc);
                            inst.page.onReady.call();
                        }
                    });
                }
            }
            return this.setCuocPhiPhatTra(false);
        }
        __current.setCuocPhiThuHo = function (data, desc) {
            this.message = null;
            var item = $.extend({}, data, {
                maHoSoOnline: hoSoOnline.maHoSo, 
                maLoaiLePhi: 8
            });
            if (data === false) {
                return this.removeLePhiHoSo(item, true);
            } else if (item.mucLePhi === null) {
                this.message = desc;
                this.removeLePhiHoSo(item, true);
                return Nth.Alert(desc);
            }
            this.editLePhiHoSo(item, desc, true);
            this.hienThiTongLePhi();
        }

        __current.tinhCuocPhiThuHo = function () {
            var tongLePhiBatBuoc = this.layTongLePhiPhaiThanhToan({thanhToanCho: 1});
            var VNPOST_CACH_TINH_PHI_THU_HO = '<?php echo \Model\Entity\System\Parameter::fromId('VNPOST_CACH_TINH_PHI_THU_HO')->getvalue(); ?>';
            if (VNPOST_CACH_TINH_PHI_THU_HO == '' || VNPOST_CACH_TINH_PHI_THU_HO == null || VNPOST_CACH_TINH_PHI_THU_HO == 'undefined') {
                return;
            }

            var cachTinh = JSON.parse(VNPOST_CACH_TINH_PHI_THU_HO);
            var tienThuHo = 0;
            if (tongLePhiBatBuoc > 0) {
                cachTinh.forEach(function(item, index) {

                    if (tongLePhiBatBuoc >= item[0] && (tongLePhiBatBuoc <= item[1] || item[1] == 'max')) {
                        if (item[2] != '' && item[2] != null && item[2] != 'undefined') {
                            tienThuHo = item[2];
                        } else {
                            tienThuHo = Math.round(tongLePhiBatBuoc * item[3]);
                        }
                        return false;
                    }

                });
                this.setCuocPhiThuHo({mucLePhi: tienThuHo, thanhToanCho: 2, maLoaiLePhi: 8, soLuong: 1, batBuocThanhToan: 0, loaiLePhi: {maLoai: 8, tenLoai: 'Cước thu hộ lệ phí qua bưu điện'}}, 'Thanh toán cho bưu điện');
            }
        }
        __current.enableHinhThucThanhToan = function (value) {
            var fs = this.getFieldset('fs-phuong-thuc-thanh-toan');
            var e = fs ? fs.findElement('maHinhThucThanhToan') : null;
            var option = e ? e.getControl().getNode().find('option[value='+ escape(value) + ']') : null;
            if (option && option.length) {
                option.removeAttr('disabled');
            }
        }
        __current.disableHinhThucThanhToan = function (value) {
            var fs = this.getFieldset('fs-phuong-thuc-thanh-toan');
            var e = fs ? fs.findElement('maHinhThucThanhToan') : null;
            var option = e ? e.getControl().getNode().find('option[value='+ escape(value) + ']') : null;
            if (option && option.length) {
                option.attr('disabled', true);
            }
        }
        __current.createHiddenInputs = function () {
            var $wrapper = $('#hidden-area-wrapper').html(null);
            $.each(this.hiddenData, function (name, value) {
                $('<input/>', {id: name, type: 'hidden', name: name, value: value}).prependTo($wrapper);
            });
        },
        __current.goNext = function () {
            $('#mainForm').attr('action', SITE_ROOT + 'bo-cong-an/tiep-nhan-online/luuLePhiHoSo').submit();
        }
        <?php if((int) Entity\System\Parameter::fromId('DOI_TUONG_GIAM_CUOC_VNPOST')->getValue() === 1): ?> //IGATESUPP-26470 tttruong-kv1
        $('#chonDoiTuong').change(function () {
            var doi_tuong = $('#chonDoiTuong').val();
            $('#chonDoiTuong').prop("selected", "selected");
            var discount = Number($(this).find(':selected').attr('data-discount'));
            var tongVnpostCu = __current.layTongLePhi({thanhToanCho: 2});
            var tongMoi = tongVnpostCu - (tongVnpostCu*discount)/100;
            $('#tongTien').html(new Nth.Helper.Number(tongMoi).addCommas().getNumber() + ' VNĐ');
            $('#txt_tienDoiTuongGiam').val(tongMoi);
        });
        <?php endif; ?>
    });
</script>
<div class="main-wrapper dvc-main-wrap nop-hs-qua-mang">
    <div class="main-content">
        <div class="container">
            <div class="box -steps">
                <div class="box-head"> Quy trình thực hiện dịch vụ công trực tuyến </div>
                <div class="box-body">
                    <div class="item active">
                        <div class="icon"> <img src="<?php echo SITE_ROOT ?>apps/dichvucong/resources/img/icons/business-cards.svg" alt=""> </div>
                        <div class="text">
                            <div class="number">1</div> Đăng ký/Đăng nhập</div>
                    </div>
                    <div class="item active">
                        <div class="icon"> <img src="<?php echo SITE_ROOT ?>apps/dichvucong/resources/img/icons/list-with-dots.svg" alt=""> </div>
                        <div class="text">
                            <div class="number">2</div><?php echo $NAME_STEP_DVC_NOP_HS_ONLINE?:'Lựa chọn DVC'; ?>
                        </div>
                    </div>
                    <div class="item active">
                        <div class="icon"> <img src="<?php echo SITE_ROOT ?>apps/dichvucong/resources/img/icons/text-file.svg" alt=""> </div>
                        <div class="text">
                            <div class="number">3</div>Nộp hồ sơ trực tuyến</div>
                    </div>
                    <div class="item">
                        <div class="icon"> <img src="<?php echo SITE_ROOT ?>apps/dichvucong/resources/img/icons/clipboard-with-a-list.svg" alt=""> </div>
                        <div class="text">
                            <div class="number">4</div>Theo dõi kết quả</div>
                    </div>
                    <div class="item">
                        <div class="icon"> <img src="<?php echo SITE_ROOT ?>apps/dichvucong/resources/img/icons/check.svg" alt=""> </div>
                        <div class="text">
                            <div class="number">5</div>Nhận kết quả</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="container">
    <section id="page-header-wrapper">
        <h3 class="page-header" id="lphs"> <?php echo ucfirst(strtolower($lb_lephi));?> hồ sơ <?php echo $hoSoOnline->getSoHoSo(); ?></h3>
        <div id="nav-wrapper">
            <?php //echo $progress->toHtml(); ?>
        </div>
        <p><?php echo $progress->getActiveStep()->getApplyNotes() ?></p>
    </section>
    <form id="mainForm" name="mainForm" action="" method="POST">
        <section id="hidden-area-wrapper"></section>
        <section id="thu-tuc-da-chon-wrapper" style="margin: 30px 0 0 0">
            <h4 style="margin-bottom:15px">
                <span class="<?php echo $mucDo->getBootstrapCss(); ?> label-fill-out"><?php echo $mucDo->getTenMucDo(); ?></span>
                <?php echo $thuTuc->getTenTat() ?> - <?php echo $thuTuc->getTenThuTuc() ?>
            </h4>
            <div class="panel panel-info">
                <table class="table">
                    <colgroup>
                        <col width="30%">
                    </colgroup>
                    <tbody>
                        <tr>
                            <td><strong>Nơi tiếp nhận hồ sơ</strong></td>
                            <td><?php echo $donViTiepNhan->getTenDonVi(); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Thời gian giải quyết</strong></td>
                            <td><?php echo $qttt->layChuoiHienThi(); ?></td>
                        </tr>
                        <?php if($qttt->getThoiGianThuGom()): ?>
                        <tr>
                            <td><strong>Thời gian thu gom (nếu có)</strong></td>
                            <td><?php echo $qttt->layThoiGianThuGomHienThi(); ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if($qttt->getThoiGianPhatTra()): ?>
                        <tr>
                            <td><strong>Thời gian phát trả kết quả (nếu có)</strong></td>
                            <td><?php echo $qttt->layThoiGianPhatTraHienThi(); ?></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
        <?php if ((int) Entity\System\Parameter::fromId('dvctt_buoclephihs_htnophs')->getValue() === 1 && $hoSoOnline->getDanhSachGiayToNop()->coGiayToPhaiNopGiay()) : ?>
        <section id="hinh-thuc-nop-wrapper" style="margin:30px 0 0 0">
            <h4><i class="fa fa-hand-o-right"></i> Hình thức nộp hồ sơ</h4>
            <p>Cá nhân hoặc tổ chức cũng có thể quyết định cách nộp hồ sơ đối với trường hợp yêu cầu nộp bản gốc, bản chính,... 
                Ngoài việc trực tiếp đem hồ sơ đến nộp cho cơ quan giải quyết thì cá nhân hoặc tổ chức còn có thể sử dụng dịch vụ thu gom của bưu điện.
                Chi phí thu gom sẽ <strong>thanh toán cho bưu điện</strong> và <strong>do bưu điện quy định</strong>.
            </p>
            <div class="form-wrapper" id="hinh-thuc-nop-form">
                <p><i class="fa fa-spinner fa-spin"></i> Loading...</p>
            </div>
            <div id="hinh-thuc-nop-action" style="display:none">
                <button type="button" data-action="get-address-from-user" class="btn btn-sm btn-default">
                    <i class="fa fa-arrow-circle-down"></i> Lấy địa chỉ của Người nộp
                </button>
                <?php if($CHON_HINH_THUC['THU_GOM_BUU_DIEN'] !=='')
                {
                    echo "<i style='color: red;'>".$CHON_HINH_THUC['THU_GOM_BUU_DIEN']."</i>";
                }
                ?>
            </div>
            <?php if($CHON_HINH_THUC['DEN_CQ_NOP_HS'] !==''){ ?>
                <div id="hinh-thuc-nop-truc-tiep-action" style="display:none">
                    <i style='color: red;'><?php echo $CHON_HINH_THUC['DEN_CQ_NOP_HS']; ?></i>
                </div>
               <?php }?>
            <script type="text/javascript">
                require(['NthLib', 'hoSoOnline'], function (Nth, hoSoOnline) {
                    var $actionWrapper = $('#hinh-thuc-nop-action');
                    var $actionWrapper_tructiep = $('#hinh-thuc-nop-truc-tiep-action');
                    var dmHinhThucNopHoSo = JSON.parse('<?php echo addslashes(json_encode($dmHinhThucNopHoSo->toArray())) ?>');
                    var fieldset = new Nth.FormBuilder.Fieldset('fs-hinh-thuc-nop');
                    var row_1 = new Nth.FormBuilder.Row(true, {
                        parentComponent: fieldset
                    });
                    var col_1 = new Nth.FormBuilder.Column(true, {
                        parentComponent: row_1,
                        customizable: false
                    });
                    var col_2 = new Nth.FormBuilder.Column(true, {
                        parentComponent: row_1,
                        hide: true
                    });
                    var col_3 = new Nth.FormBuilder.Column(true, {
                        parentComponent: row_1,
                        hide: true
                    });
                    var col_4 = new Nth.FormBuilder.Column(true, {
                        parentComponent: row_1,
                        hide: true
                    });
                    var col_5 = new Nth.FormBuilder.Column(true, {
                        parentComponent: row_1,
                        hide: true
                    });
                    var col_6 = new Nth.FormBuilder.Column(true, {
                        parentComponent: row_1,
                        hide: true
                    });
                    <?php if($YBI_BCCI): // Hunglv.ybi IGATESUPP-12290?>
                    var ybi_col_1 = new Nth.FormBuilder.Column(true, {
                        parentComponent: row_1,
                        hide: true
                    });
                    var ybi_col_2 = new Nth.FormBuilder.Column(true, {
                        parentComponent: row_1,
                        hide: true
                    });
                    <?php endif;?>
                    var maHinhThucNop = new Nth.FormBuilder.Element.Select('maHinhThucNop', {
                        parentComponent: col_1,
                        label: 'Hình thức nộp hồ sơ',
                        // marksMandatory: false,
                        selectItemData: new Nth.List(dmHinhThucNopHoSo).toHtmlOptions(function(item) {
                            return item.maHinhThuc
                        }, function (item) {
                            return item.tenHinhThuc
                        },null,"<?php echo ($CHON_HINH_THUC['CHON_HINH_THUC_NOP'] ==='1')?'-- Chọn hình thức nộp --':'' ?>"),
                        required: true,
                        defaultValue: 0,
                        value: hoSoOnline.maHinhThucNop,
                        autoInit: false,
                        customizable: false,
                        onvalid: function () {
                            hoSoOnline.maHinhThucNop = this.getValue();
                            hoSoOnline.hinhThucNop.maHinhThuc = hoSoOnline.maHinhThucNop;
                            hoSoOnline.hinhThucNop.tenHinhThuc = this.getSelectedText();
                        }
                    });
                    maHinhThucNop.getWrapper().getNode().on('nth.fb.bound.htnhs', function () {
                        $.each(fieldset.getComponents(), function (i, c) {
                            if (c instanceof Nth.FormBuilder.Element) {
                                if (c.getOption('customizable', true)) {
                                    c.setRequired(false);
                                }
                            } else if (c instanceof Nth.FormBuilder.Column) {
                                if (c.getOption('customizable', true)) {
                                    c.hide();
                                }
                            }
                        });
                        if (parseInt(maHinhThucNop.getValue()) === 1) {
                            $actionWrapper.show();
                            $actionWrapper_tructiep.hide();
                            __current.enableHinhThucThanhToan(6);
                            var phuongXaThuGom_quanHuyen_maTinhThanh = fieldset.getComponent('phuongXaThuGom_quanHuyen_maTinhThanh');
                            var phuongXaThuGom_maQuanHuyen = fieldset.getComponent('phuongXaThuGom_maQuanHuyen');
                            var maPhuongXaThuGom = fieldset.getComponent('maPhuongXaThuGom');
                            var diaChiThuGom = fieldset.getComponent('diaChiThuGom');
                            var ngayYeuCauThuGom = fieldset.getComponent('ngayYeuCauThuGom');
                            if (!phuongXaThuGom_quanHuyen_maTinhThanh) {
                                phuongXaThuGom_quanHuyen_maTinhThanh = new Nth.FormBuilder.Element.Select('phuongXaThuGom_quanHuyen_maTinhThanh', {
                                    parentComponent: col_2,
                                    bindName: 'P_MA_TINH_THANH',
                                    label: 'Tỉnh/TP thu gom',
                                    selectItemUrl: 'model/htmloption/DM_TINH_THANH',
                                    value: hoSoOnline.phuongXaThuGom.quanHuyen.maTinhThanh || MA_TINH_THANH,
                                    onvalid: function () {
                                        hoSoOnline.phuongXaThuGom.quanHuyen.maTinhThanh = this.getValue();
                                        hoSoOnline.phuongXaThuGom.quanHuyen.tinhThanh.maTinhThanh = hoSoOnline.phuongXaThuGom.quanHuyen.maTinhThanh;
                                        hoSoOnline.phuongXaThuGom.quanHuyen.tinhThanh.tenTinhThanh = this.getSelectedText();
                                    }
                                });
                                fieldset.addComponent(phuongXaThuGom_quanHuyen_maTinhThanh);
                            }
                            if (!phuongXaThuGom_maQuanHuyen) {
                                phuongXaThuGom_maQuanHuyen = new Nth.FormBuilder.Element.Select('phuongXaThuGom_maQuanHuyen', {
                                    parentComponent: col_3,
                                    bindName: 'P_MA_QUAN_HUYEN',
                                    label: 'Quận/Huyện thu gom',
                                    selectItemUrl: 'model/htmloption/DM_QUAN_HUYEN',
                                    bindBy: phuongXaThuGom_quanHuyen_maTinhThanh,
                                    value: hoSoOnline.phuongXaThuGom.maQuanHuyen,
                                    onvalid: function () {
                                        hoSoOnline.phuongXaThuGom.maQuanHuyen = this.getValue();
                                        hoSoOnline.phuongXaThuGom.quanHuyen.maQuanHuyen = hoSoOnline.phuongXaThuGom.maQuanHuyen;
                                        hoSoOnline.phuongXaThuGom.quanHuyen.tenQuanHuyen = this.getSelectedText();
                                    }
                                });
                                fieldset.addComponent(phuongXaThuGom_maQuanHuyen);
                            }
                            if (!maPhuongXaThuGom) {
                                maPhuongXaThuGom = new Nth.FormBuilder.Element.Select('maPhuongXaThuGom', {
                                    parentComponent: col_4,
                                    bindName: 'P_MA_PHUONG_XA',
                                    label: 'Phường/Xã/Thị trấn thu gom',
                                    selectItemUrl: 'model/htmloption/DM_PHUONG_XA',
                                    bindBy: [phuongXaThuGom_quanHuyen_maTinhThanh, phuongXaThuGom_maQuanHuyen],
                                    value: hoSoOnline.maPhuongXaThuGom,
                                    onvalid: function () {
                                        hoSoOnline.maPhuongXaThuGom = this.getValue();
                                        hoSoOnline.phuongXaThuGom.maPhuongXa = hoSoOnline.maPhuongXaThuGom;
                                        hoSoOnline.phuongXaThuGom.tenPhuongXa = this.getSelectedText();
                                    }
                                });
                                maPhuongXaThuGom.getWrapper().getNode().on('nth.fb.bound.lphs', function () {
                                    __current.tinhCuocPhiThuGom();
                                });
                                fieldset.addComponent(maPhuongXaThuGom);
                            } else {
                                maPhuongXaThuGom.bound();
                            }
                            if (!diaChiThuGom) {
                                diaChiThuGom = new Nth.FormBuilder.Element.Text('diaChiThuGom', {
                                    parentComponent: col_5,
                                    label: 'Số nhà/Đường/Tổ/Ấp/Thôn/Xóm thu gom',
                                    value: hoSoOnline.diaChiThuGom,
                                    onvalid: function () {
                                        hoSoOnline.diaChiThuGom = this.getValue();
                                    }
                                }); 
                                fieldset.addComponent(diaChiThuGom);
                            }
                            if (!ngayYeuCauThuGom) {
                                ngayYeuCauThuGom = new Nth.FormBuilder.Element.DateTime('ngayYeuCauThuGom', {
                                    parentComponent: col_6,
                                    label: 'Ngày giờ yêu cầu thu gom',
                                    formats: [{format: 'DD/MM/YYYY HH:mm', defaultValue: new Date() }],
                                    value: hoSoOnline.ngayYeuCauThuGom || new Date(),
                                    onvalid: function () {
                                        hoSoOnline.ngayYeuCauThuGom = this.getValue();
                                    }
                                });
                                fieldset.addComponent(ngayYeuCauThuGom);
                            }
                            col_2.show();
                            col_3.show();
                            col_4.show();
                            col_5.show();
                            col_6.show();
                            <?php if($YBI_BCCI): // Hunglv.ybi IGATESUPP-12290?>
                            tenCongDan = new Nth.FormBuilder.Element.Text('tenNguoiNhanKetQua', {
                                parentComponent: ybi_col_1,
                                label: 'Họ và tên',
                                value: hoSoOnline.congDan.tenCongDan,
                                controlAttributes: {
                                    readonly: true
                                }
                            });
                            fieldset.addComponent(tenCongDan);
                            diDong = new Nth.FormBuilder.Element.Text('sdtNguoiNhanKetQua', {
                                parentComponent: ybi_col_2,
                                label: 'Số điện thoại',
                                value: hoSoOnline.congDan.diDong,
                                controlAttributes: {
                                    readonly: true
                                }
                            });
                            fieldset.addComponent(diDong);
                            ybi_col_1.show();
                            ybi_col_2.show();
                            <?php endif;?>
                            phuongXaThuGom_quanHuyen_maTinhThanh.setRequired(true);
                            phuongXaThuGom_maQuanHuyen.setRequired(true);
                            maPhuongXaThuGom.setRequired(true);
                            diaChiThuGom.setRequired(true);
                            ngayYeuCauThuGom.setRequired(true);
                            $actionWrapper.find('button[data-action=get-address-from-user]').off('click').on('click', function () {
                                phuongXaThuGom_quanHuyen_maTinhThanh.setValue(hoSoOnline.congDan.phuongXa.quanHuyen.maTinhThanh);
                                phuongXaThuGom_maQuanHuyen.setValue(hoSoOnline.congDan.phuongXa.maQuanHuyen);
                                maPhuongXaThuGom.setValue(hoSoOnline.congDan.maPhuongXa);
                                diaChiThuGom.setValue(hoSoOnline.congDan.diaChi);
                                phuongXaThuGom_quanHuyen_maTinhThanh.bound();
                            });
                        } else if (parseInt(maHinhThucNop.getValue()) === 0){
                            $actionWrapper_tructiep.show();
                            $actionWrapper.hide();
                            __current.setCuocPhiThuGom(false);
                            __current.disableHinhThucThanhToan(6);
                        }
                        else {
                            $actionWrapper.hide();
                            $actionWrapper_tructiep.hide();
                            __current.setCuocPhiThuGom(false);
                            __current.disableHinhThucThanhToan(6);
                        }
                    });
                    fieldset.addComponent(row_1, col_1, col_2, col_3, col_4, col_5, col_6, maHinhThucNop.init());
                    <?php if($YBI_BCCI): // Hunglv.ybi IGATESUPP-12290?>
                    fieldset.addComponent(ybi_col_1, ybi_col_2);
                    <?php endif;?>
                    $('#hinh-thuc-nop-form').html(fieldset.getDomNode());
                    __current.fs.push(fieldset);
                });
            </script>
        </section>
        <?php endif; ?>
        <?php if((int) Entity\System\Parameter::fromId('dvctt_buoclephihs_htnhankqhs')->getValue() === 1): ?>
        <section id="hinh-thuc-nhan-ket-qua-wrapper" style="margin:30px 0 0 0">
            <h4><i class="fa fa-hand-o-right"></i> Hình thức nhận kết quả</h4>
            <p>Cá nhân hoặc tổ chức chọn nơi nhận hồ sơ khi đã giải quyết xong. Đối với trường hợp nhờ bưu điện phát trả kết quả
                thì kết quả hồ sơ sẽ được gửi đến địa chỉ bên dưới thông qua bưu điện, các chi phí gửi kết quả sẽ do <strong>bưu điện</strong> hoặc <strong>cơ quan giải quyết</strong> quy định.
            </p>
            <div class="form-wrapper" id="hinh-thuc-nhan-ket-qua-form">
                <p><i class="fa fa-spin fa-spinner"></i> Loading...</p>
            </div>
            <div id="hinh-thuc-nhan-ket-qua-action" style="display:none">
                <button type="button" data-action="get-address-from-user" class="btn btn-sm btn-default">
                    <i class="fa fa-arrow-circle-down"></i> Lấy địa chỉ của Người nộp
                </button>
                <?php if($CHON_HINH_THUC['GUI_DEN_DIA_CHI'] !=='')
                {
                    echo "<i style='color: red;'>".$CHON_HINH_THUC['GUI_DEN_DIA_CHI']."</i>";
                }
                ?>
            </div>
            <?php if($CHON_HINH_THUC['DEN_CQ_NHAN_KQ'] !==''){ ?>
                <div id="hinh-thuc-nhan-ket-qua-truc-tiep-action" style="display:none">
                    <i style='color: red;'><?php echo $CHON_HINH_THUC['DEN_CQ_NHAN_KQ']; ?></i>
                </div>
               <?php }?>
            <script type="text/javascript">
                var VNPOST_NHAN_HS_ONLINE = <?php   if($VNPOST_NHAN_HS_ONLINE) {
                                                        echo $VNPOST_NHAN_HS_ONLINE;
                                                    } else {
                                                        echo "''";
                                                    } ?>;
                                                    console.log(VNPOST_NHAN_HS_ONLINE);
                require(['NthLib', 'hoSoOnline'], function (Nth, hoSoOnline) {
                    var $actionWrapper = $('#hinh-thuc-nhan-ket-qua-action');
                    var $actionWrapper_tructiep = $('#hinh-thuc-nhan-ket-qua-truc-tiep-action');
                    var dmHinhThucNhanKetQua = JSON.parse('<?php echo addslashes(json_encode($dmHinhThucNhanKetQua->toArray())) ?>');
                    var fieldset = new Nth.FormBuilder.Fieldset('fs-noi-nhan-ket-qua');
                    var row_1  = new Nth.FormBuilder.Row('row-1', {
                        parentComponent: fieldset
                    });
                    var column_1  = new Nth.FormBuilder.Column('column-1', {
                        parentComponent: row_1,
                        customizable: false
                    });
                    var column_2  = new Nth.FormBuilder.Column('column-2', {
                        parentComponent: row_1,
                        hide: true
                    });
                    var column_3  = new Nth.FormBuilder.Column('column-3', {
                        parentComponent: row_1,
                        hide: true
                    });
                    var column_4  = new Nth.FormBuilder.Column('column-4', {
                        parentComponent: row_1,
                        hide: true
                    });
                    var column_5  = new Nth.FormBuilder.Column('column-5', {
                        parentComponent: row_1,
                        hide: true
                    });
                    <?php if($YBI_BCCI): // Hunglv.ybi IGATESUPP-12290?>
                    var ybi_column_1 = new Nth.FormBuilder.Column('ybi-column-1', {
                        parentComponent: row_1,
                        hide: true
                    });
                    var ybi_column_2 = new Nth.FormBuilder.Column('ybi-column-2', {
                        parentComponent: row_1,
                        hide: true
                    });
                    <?php endif;?>
                    if (VNPOST_NHAN_HS_ONLINE == "1"){
                        var vpc_column_1 = new Nth.FormBuilder.Column('vpc-column-1', {
                            parentComponent: row_1,
                            hide: true
                        });
                        var vpc_column_2 = new Nth.FormBuilder.Column('vpc-column-2', {
                            parentComponent: row_1,
                            hide: true
                        });
                    }
                    var maHinhThucNhanKetQua = new Nth.FormBuilder.Element.Select('maHinhThucNhanKetQua', {
                        parentComponent: column_1,
                        label: 'Hình thức nhận kết quả',
                        selectItemData: new Nth.List(dmHinhThucNhanKetQua).toHtmlOptions(function (item) {
                            return item.maHinhThuc
                        }, function (item) {
                            return item.tenHinhThuc
                        },null,"<?php echo ($CHON_HINH_THUC['CHON_HINH_THUC_NHAN_KQ'] ==='1')?'-- Chọn hình thức nhận kết quả --':'' ?>"),
                        required: true,
                        defaultValue: 0,
                        value: hoSoOnline.maHinhThucNhanKetQua,
                        autoInit: false,
                        customizable: false,
                        onvalid: function () {
                            hoSoOnline.maHinhThucNhanKetQua = this.getValue();
                            hoSoOnline.hinhThucNhanKetQua.maHinhThuc = hoSoOnline.maHinhThucNhanKetQua;
                            hoSoOnline.hinhThucNhanKetQua.tenHinhThuc = this.getSelectedText();
                        }
                    });
                    maHinhThucNhanKetQua.getWrapper().getNode().on('nth.fb.bound.dcnkq', function() {
                        $.each(fieldset.getComponents(), function (i, c) {
                            if (c instanceof Nth.FormBuilder.Element) {
                                if (c.getOption('customizable', true)) {
                                    c.setRequired(false);
                                }
                            } else if (c instanceof Nth.FormBuilder.Column) {
                                if (c.getOption('customizable', true)) {
                                    c.hide();
                                }
                            }
                        });
                        
                        if (parseInt(maHinhThucNhanKetQua.getValue()) === 1 || (parseInt(maHinhThucNhanKetQua.getValue()) === 6 && VNPOST_NHAN_HS_ONLINE == "1")) {
                            $actionWrapper.show();
                            $actionWrapper_tructiep.hide();
                            var phuongXaNhanKetQua_quanHuyen_maTinhThanh = fieldset.getComponent('phuongXaNhanKetQua_quanHuyen_maTinhThanh');
                            var phuongXaNhanKetQua_maQuanHuyen = fieldset.getComponent('phuongXaNhanKetQua_maQuanHuyen');
                            var maPhuongXaNhanKetQua = fieldset.getComponent('maPhuongXaNhanKetQua');
                            var diaChiNhanKetQua = fieldset.getComponent('diaChiNhanKetQua');
                            if (!phuongXaNhanKetQua_quanHuyen_maTinhThanh) {
                                phuongXaNhanKetQua_quanHuyen_maTinhThanh = new Nth.FormBuilder.Element.Select('phuongXaNhanKetQua_quanHuyen_maTinhThanh', {
                                    parentComponent: column_2,
                                    bindName: 'P_MA_TINH_THANH',
                                    label: 'Tỉnh/TP nhận kết quả',
                                    selectItemUrl: 'model/htmloption/DM_TINH_THANH',
                                    value: hoSoOnline.phuongXaNhanKetQua.quanHuyen.maTinhThanh || MA_TINH_THANH,
                                    onvalid: function () {
                                        hoSoOnline.phuongXaNhanKetQua.quanHuyen.maTinhThanh = this.getValue();
                                        hoSoOnline.phuongXaNhanKetQua.quanHuyen.tinhThanh.maTinhThanh = hoSoOnline.phuongXaNhanKetQua.quanHuyen.maTinhThanh;
                                        hoSoOnline.phuongXaNhanKetQua.quanHuyen.tinhThanh.tenTinhThanh = this.getSelectedText();
                                    }
                                });
                                fieldset.addComponent(phuongXaNhanKetQua_quanHuyen_maTinhThanh);
                            }
                            if (!phuongXaNhanKetQua_maQuanHuyen) {
                                phuongXaNhanKetQua_maQuanHuyen = new Nth.FormBuilder.Element.Select('phuongXaNhanKetQua_maQuanHuyen', {
                                    parentComponent: column_3,
                                    bindName: 'P_MA_QUAN_HUYEN',
                                    label: 'Quận/Huyện nhận kết quả',
                                    selectItemUrl: 'model/htmloption/DM_QUAN_HUYEN',
                                    bindBy: phuongXaNhanKetQua_quanHuyen_maTinhThanh,
                                    value: hoSoOnline.phuongXaNhanKetQua.maQuanHuyen,
                                    onvalid: function () {
                                        hoSoOnline.phuongXaNhanKetQua.maQuanHuyen = this.getValue();
                                        hoSoOnline.phuongXaNhanKetQua.quanHuyen.maQuanHuyen = hoSoOnline.phuongXaNhanKetQua.maQuanHuyen;
                                        hoSoOnline.phuongXaNhanKetQua.quanHuyen.tenQuanHuyen = this.getSelectedText();
                                    }
                                });
                                fieldset.addComponent(phuongXaNhanKetQua_maQuanHuyen);
                            }
                            if (!maPhuongXaNhanKetQua) {
                                maPhuongXaNhanKetQua = new Nth.FormBuilder.Element.Select('maPhuongXaNhanKetQua', {
                                    parentComponent: column_4,
                                    bindName: 'P_MA_PHUONG_XA',
                                    label: 'Phường/Xã/Thị trấn nhận kết quả',
                                    selectItemUrl: 'model/htmloption/DM_PHUONG_XA',
                                    bindBy: [phuongXaNhanKetQua_quanHuyen_maTinhThanh, phuongXaNhanKetQua_maQuanHuyen],
                                    value: hoSoOnline.maPhuongXaNhanKetQua,
                                    onvalid: function () {
                                        hoSoOnline.maPhuongXaNhanKetQua = this.getValue();
                                        hoSoOnline.phuongXaNhanKetQua.maPhuongXa = hoSoOnline.maPhuongXaNhanKetQua;
                                        hoSoOnline.phuongXaNhanKetQua.tenPhuongXa = this.getSelectedText();
                                    }
                                });
                                maPhuongXaNhanKetQua.getWrapper().getNode().on('nth.fb.bound.lphs', function () {
                                    __current.tinhCuocPhiPhatTra();
                                });
                                fieldset.addComponent(maPhuongXaNhanKetQua);
                            } else {
                                fieldset.getComponent('maPhuongXaNhanKetQua').bound();
                            }
                            if (!diaChiNhanKetQua) {
                                var label = "";
                                if (VNPOST_NHAN_HS_ONLINE == "1") {
                                        label = 'Địa chỉ nhận kết quả';
                                    } else {
                                        label = 'Số nhà/Đường/Tổ/Ấp/Thôn/Xóm nhận kết quả';
                                    }
                                diaChiNhanKetQua = new Nth.FormBuilder.Element.Text('diaChiNhanKetQua', {
                                    parentComponent: column_5,
                                    label: label,
                                    value: hoSoOnline.diaChiNhanKetQua,
                                    onvalid: function () {
                                        hoSoOnline.diaChiNhanKetQua = this.getValue();
                                    }
                                });
                                fieldset.addComponent(diaChiNhanKetQua);
                            }
                            column_2.show();
                            column_3.show();
                            column_4.show();
                            column_5.show();
                            <?php if($YBI_BCCI): // Hunglv.ybi IGATESUPP-12290?>
                            tenCongDan = new Nth.FormBuilder.Element.Text('tenNguoiNhanKetQua', {
                                parentComponent: ybi_column_1,
                                label: 'Họ và tên',
                                value: hoSoOnline.congDan.tenCongDan,
                                controlAttributes: {
                                    readonly: true
                                }
                            });
                            fieldset.addComponent(tenCongDan);
                            diDong = new Nth.FormBuilder.Element.Text('sdtNguoiNhanKetQua', {
                                parentComponent: ybi_column_2,
                                label: 'Số điện thoại',
                                value: hoSoOnline.congDan.diDong,
                                controlAttributes: {
                                    readonly: true
                                }
                            });
                            fieldset.addComponent(diDong);
                            ybi_column_1.show();
                            ybi_column_2.show();
                            <?php endif;?>
                            if (VNPOST_NHAN_HS_ONLINE == "1") {
                                tenNguoiNhanKetQuaVpc = new Nth.FormBuilder.Element.Text('tenNguoiNhanKetQuaVpc', {
                                    parentComponent: vpc_column_1,
                                    label: 'Họ và tên người nhận',
                                    value: hoSoOnline.congDan.tenCongDan,
                                    onvalid: function () {
                                        hoSoOnline.tenNguoiNhanKetQuaVpc = this.getValue();
                                    }
                                });
                                fieldset.addComponent(tenNguoiNhanKetQuaVpc);
                                sdtNguoiNhanKetQuaVpc = new Nth.FormBuilder.Element.Text('sdtNguoiNhanKetQuaVpc', {
                                    parentComponent: vpc_column_2,
                                    label: 'Số điện thoại người nhận',
                                    value: hoSoOnline.congDan.diDong,
                                    onvalid: function () {
                                        hoSoOnline.sdtNguoiNhanKetQuaVpc = this.getValue();
                                    }
                                });
                                fieldset.addComponent(sdtNguoiNhanKetQuaVpc);
                                vpc_column_1.show();
                                vpc_column_2.show();
                                tenNguoiNhanKetQuaVpc.setRequired(true);
                                sdtNguoiNhanKetQuaVpc.setRequired(true);
                            }
                            phuongXaNhanKetQua_quanHuyen_maTinhThanh.setRequired(true);
                            phuongXaNhanKetQua_maQuanHuyen.setRequired(true);
                            maPhuongXaNhanKetQua.setRequired(true);
                            diaChiNhanKetQua.setRequired(true);
                            $actionWrapper.find('button[data-action=get-address-from-user]').off('click').on('click', function () {
                                phuongXaNhanKetQua_quanHuyen_maTinhThanh.setValue(hoSoOnline.congDan.phuongXa.quanHuyen.maTinhThanh);
                                phuongXaNhanKetQua_maQuanHuyen.setValue(hoSoOnline.congDan.phuongXa.maQuanHuyen);
                                maPhuongXaNhanKetQua.setValue(hoSoOnline.congDan.maPhuongXa);
                                diaChiNhanKetQua.setValue(hoSoOnline.congDan.diaChi);
                                phuongXaNhanKetQua_quanHuyen_maTinhThanh.bound();
                            });
                            if (VNPOST_NHAN_HS_ONLINE == "1") {
                                phuongXaNhanKetQua_quanHuyen_maTinhThanh.setValue(hoSoOnline.congDan.phuongXa.quanHuyen.maTinhThanh);
                                phuongXaNhanKetQua_maQuanHuyen.setValue(hoSoOnline.congDan.phuongXa.maQuanHuyen);
                                maPhuongXaNhanKetQua.setValue(hoSoOnline.congDan.maPhuongXa);
                                diaChiNhanKetQua.setValue(hoSoOnline.congDan.diaChi);
                                phuongXaNhanKetQua_quanHuyen_maTinhThanh.bound();
                            }
                        } else if (parseInt(maHinhThucNhanKetQua.getValue()) === 0){
                            $actionWrapper_tructiep.show();
                            $actionWrapper.hide();
                            __current.setCuocPhiPhatTra(false);
                        }
                        else {
                            $actionWrapper.hide();
                            $actionWrapper_tructiep.hide();
                            __current.setCuocPhiPhatTra(false);}
                    });
                    fieldset.addComponent(row_1);
                    fieldset.addComponent(column_1);
                    fieldset.addComponent(column_2);
                    fieldset.addComponent(column_3);
                    fieldset.addComponent(column_4);
                    fieldset.addComponent(column_5);
                    <?php if($YBI_BCCI): // Hunglv.ybi IGATESUPP-12290 ?>
                    fieldset.addComponent(ybi_column_1, ybi_column_2);
                    <?php endif;?>
                    if (VNPOST_NHAN_HS_ONLINE == "1"){
                        fieldset.addComponent(vpc_column_1, vpc_column_2);
                    }
                    fieldset.addComponent(maHinhThucNhanKetQua.init());
                    $('#hinh-thuc-nhan-ket-qua-form').html(fieldset.getDomNode());
                    __current.fs.push(fieldset);
                });
            </script>
        </section>
        <?php endif; ?>
        <style type="text/css">
            #le-phi-nop-1-wrapper td .form-group{margin: 0}
            #le-phi-nop-1-wrapper td .form-group .form-control{padding: 0;height: inherit;border: none;box-shadow: none;-webkit-box-shadow: none;}
            #le-phi-nop-1-wrapper td .form-group.has-error .form-control{border-bottom: 2px solid #d83f11;padding-bottom: 2px;}
        </style>
        <section id="le-phi-nop-1-wrapper" style="margin:30px 0 0 0">
           
            <?php if(Entity\System\Parameter::fromId('LABEL_HO_SO_ONLINE_LAN')->getValue() ==1){
            ?>
                <h4 style="color:#d8ac0b"><i class="fa fa-money"></i> <?php echo ucfirst(strtolower($lb_lephi));?> giải quyết thủ tục hành chính :  <strong><span id="tongLePhi"> 0 </span> đồng</strong></h4>
            <?php
            }
            else
            {?>
                <h4 style="color:#d8ac0b"><i class="fa fa-money"></i> <?php echo ucfirst(strtolower($lb_lephi));?> thanh toán cho cơ quan giải quyết <small>Đơn vị tính: <strong>VNĐ</strong></small></h4>
            <?php
            }?>
            <p>Các khoản <?php echo strtolower($lb_lephi);?> mà cá nhân hoặc tổ chức phải thanh toán cho cơ quan giải quyết.</p>
            <div class="form-wrapper">
                <div class="panel panel-info">
                    <table class="table">
                        <colgroup>
                            <col width="30%"/>
                            <col width="10%"/>
                            <col width="20%"/>
                            <col width="5%"/>
                            <col width="10%"/>
                            <col width="30%"/>
                        </colgroup>
                        <thead>
                            <tr>
                                <td><strong>Loại <?php echo strtolower($lb_lephi);?></strong></td>
                                <td data-toggle="tooltip" title="Số lượng giấy tờ mà người nộp hồ sơ muốn cơ quan giải quyết "><strong>Số lượng</strong></td>
                                <td><strong>Mức <?php echo strtolower($lb_lephi);?></strong></td>
                                <td></td>
                                <td data-toggle="tooltip" title="<?php echo ucfirst(strtolower($lb_lephi));?> bắt buộc phải thanh toán khi nộp hồ sơ online"><strong>Bắt buộc</strong></td>
                                <td><strong>Mô tả</strong></td>
                            </tr>
                        </thead>
                        <tbody id="tbody-1"></tbody>
                        <tfoot>
                            <tr id="tong-cong-1">
                                <td><strong>Tổng <?php echo strtolower($lb_lephi);?></strong></td>
                                <td class="soLuong"></td>
                                <td class="amount total">0</td>
                                <td class="unit"><strong>VNĐ</strong></td>
                                <td class="require"></td>
                                <td class="desc"></td>
                            </tr>
                            <tr id="tong-le-phi-bat-buoc">
                                <td><strong>Tổng <?php echo strtolower($lb_lephi);?> bắt buộc phải đóng trước</strong></td>
                                <td class="soLuong"></td>
                                <td class="amount total">0</td>
                                <td class="unit"><strong>VNĐ</strong></td>
                                <td class="require"></td>
                                <td class="desc"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <script type="text/javascript">
                require(['NthLib', 'hoSoOnline'], function (Nth, hoSoOnline) {
                    var dmLoaiLePhiCuaThuTuc = JSON.parse('<?php echo addslashes(json_encode($dmLoaiLePhiCuaThuTuc->toArray())) ?>');
                    var bca_phi_tt = parseInt('<?php echo ($bca_phi_tt!='')?str_replace(',', '', $bca_phi_tt):0;   ?>');
                    var BCA_TINH_PHI_TT_NEW = '<?php echo $BCA_TINH_PHI_TT_NEW;   ?>';
                    var disable_so_luong=false;
                    if (BCA_TINH_PHI_TT_NEW=='1' && bca_phi_tt !=0) {
                        disable_so_luong=true;
                    }
                    if(dmLoaiLePhiCuaThuTuc.length != 0){
                        var fieldset = new Nth.FormBuilder.Fieldset('fs-le-phi-ho-so');
                        var $tbody = $('#le-phi-nop-1-wrapper tbody');
                        $.each(dmLoaiLePhiCuaThuTuc, function (i, item) {
                            var choosed = $.extend({}, hoSoOnline.dmLePhiHoSo.filter(function (a) {
                                return parseInt(a.maLoaiLePhi) === parseInt(item.maLoai);
                            })[0]);
                            
                            var caThiSoLuongDK = parseInt('<?php echo $caThiSoLuongDK;   ?>');
                            var loaiLePhiCaThi = parseInt('<?php echo \Model\Entity\System\Parameter::fromId('dvc_ca_thi_ma_loai_le_phi',['cache' => false])->getValue();   ?>') || 1;
                            
                            if(item.maLoai == loaiLePhiCaThi && caThiSoLuongDK > 0 ){
                                choosed.soLuong = caThiSoLuongDK;
                            }
                            if (choosed.soLuong === undefined || choosed.soLuong == null || choosed.soLuong.length < 0) {
                                choosed.soLuong = 1;
                            }
                            if (choosed.daThanhToan === undefined || choosed.daThanhToan == null || choosed.daThanhToan.length < 0) {
                                choosed.daThanhToan = 0;
                            }
                            if (choosed.batBuocThanhToan === undefined || choosed.batBuocThanhToan == null || choosed.batBuocThanhToan.length < 0){
                                choosed.batBuocThanhToan = item.batBuocThanhToan;
                            }
                            var $tr = $('<tr/>', {id: 'loai-le-phi-' + item.maLoai}).prependTo($tbody);
                            var $td1 = $('<td/>', {class: 'name'}).html(item.tenLoai);
                            var $td5 = $('<td/>', {class: 'number'});
                            var $td2 = $('<td/>', {class: 'amount'});
                            var $td6 = $('<td/>', {class: 'require'}).html(choosed.batBuocThanhToan == 1 ? '<strong>Có</strong>' : '<strong>Không</strong>');
                            var $td3 = $('<td/>', {class: 'unit'}).html('<strong>VNĐ</strong>');
                            var paidMessage = parseInt(choosed.daThanhToan) == 1 ? 'Đã thanh toán':'';
                            var $td4 = $('<td/>', {class: 'desc',id:'feeDesc_'+item.maLoai,paid: choosed.daThanhToan}).html( '\n' + paidMessage);
                            if (paidMessage.length > 0){
                                $tr.addClass("paid");
                            }
                            var soLuong = new Nth.FormBuilder.Element.Text('soLuong_' + item.maLoai, {
                                parentComponent: fieldset,
                                label: false,
                                required: true,
                                addingRelative: $td5,
                                messageContainer: $td4,
                                value: choosed.soLuong,
                                disabled:disable_so_luong,
                                controlAttributes: {
                                    class: 'form-control',
                                    placeholder: 'Số lượng giấy tờ',
                                    style: 'border:1px solid #C0C0C0FF',
                                    readonly: (parseInt(choosed.daThanhToan) == 1 || parseInt(item.choPhepNhapSoLuong) == 0) ? true : false
                                },
                                validators: [new Nth.Validator.Number({
                                    maxLength: 38,
                                    min: 1
                                })]
                            });
                            var loaiLePhi = new Nth.FormBuilder.Element.Select('loaiLePhi_' + item.maLoai, {
                                parentComponent: fieldset,
                                label: false,
                                required: true,
                                validName: item.tenLoai,
                                addingRelative: $td2,
                                messageContainer: $td4,
                                value: choosed.maLePhiThuTuc,
                                disabled: parseInt(choosed.daThanhToan),
                                change: function(){
                                    var option = $('select[name^=loaiLePhi_'+item.maLoai+']').find('option:selected');
                                    if(typeof option != 'undefined'){
                                        var $tdFee = $('#feeDesc_'+item.maLoai);
                                        var  daThanhToan = $tdFee.attr('paid') == '1' ? 'Đã thanh toán':'';
                                        var desc = $(option).attr('desc');
                                        if(typeof desc !='undefined'){
                                            $tdFee.html(desc+'\n'+daThanhToan);
                                        }
                                    }
                                    
                                },
                                selectItemUrl: 'public/selectitems/lePhiThuTuc',
                                selectItemRequestData: {
                                    maThuTuc: hoSoOnline.qttt.maThuTuc,
                                    maLoai: item.maLoai,
                                    trangThai: 1,
                                    ttOnline: [1,2],
                                    maQttt: hoSoOnline.qttt.maQttt,
                                    bca_phi_tt: bca_phi_tt
                                }
                            });
                            loaiLePhi.getWrapper().getNode().on('nth.fb.bound', function () {
                                var value = loaiLePhi.getValue();
                                var sl = soLuong.getValue();
                                if (value) {
                                    __current.editLePhiHoSo({
                                        maLePhiThuTuc: parseInt(value),
                                        maLoaiLePhi: parseInt(item.maLoai),
                                        mucLePhi: parseInt(loaiLePhi.getSelectedItem().data('muc-le-phi')),
                                        thanhToanCho: 1,
                                        soLuong: parseInt(sl),
                                        batBuocThanhToan: parseInt(choosed.batBuocThanhToan),
                                        loaiLePhi: item
                                    });
                                    var hinhThucThanhToan = $('#_fcmaHinhThucThanhToan').val();
                                    if (hinhThucThanhToan == 6) {
                                        __current.removeLePhiHoSo({mucLePhi: 5500, thanhToanCho: 1, maLoaiLePhi: 8, loaiLePhi: {maLoai: 8, tenLoai: 'Cước thanh toán thu hộ lệ phí bưu điện'}}, true);
                                        __current.tinhCuocPhiThuHo();
                                        __current.hienThiTongLePhi();
                                    }
                                } else {
                                    __current.removeLePhiHoSo({
                                        maLoaiLePhi: parseInt(item.maLoai)
                                    });
                                }
                            });
                            soLuong.getWrapper().getNode().on('change', function () {
                                var value = loaiLePhi.getValue();
                                var sl = soLuong.getValue();
                                if (value) {
                                    __current.editLePhiHoSo({
                                        maLePhiThuTuc: parseInt(value),
                                        maLoaiLePhi: parseInt(item.maLoai),
                                        mucLePhi: parseInt(loaiLePhi.getSelectedItem().data('muc-le-phi')),
                                        thanhToanCho: 1,
                                        soLuong: parseInt(sl),
                                        batBuocThanhToan: parseInt(choosed.batBuocThanhToan),
                                        loaiLePhi: item
                                    });
                                    var hinhThucThanhToan = $('#_fcmaHinhThucThanhToan').val();
                                    if (hinhThucThanhToan == 6) {
                                        __current.removeLePhiHoSo({mucLePhi: 5500, thanhToanCho: 1, maLoaiLePhi: 8, loaiLePhi: {maLoai: 8, tenLoai: 'Cước thanh toán thu hộ lệ phí bưu điện'}}, true);
                                        __current.tinhCuocPhiThuHo();
                                        __current.hienThiTongLePhi();
                                    }
                                } else {
                                    __current.removeLePhiHoSo({
                                        maLoaiLePhi: parseInt(item.maLoai)
                                    });
                                }
                            });
                            $tr.append($td1, $td5, $td2, $td3, $td6, $td4);
                            fieldset.addComponent(soLuong);
                            fieldset.addComponent(loaiLePhi);
                        });
                        __current.fs.push(fieldset);
                    }else{
                        $('#le-phi-nop-1-wrapper').hide();
                        $('#le-phi-nop-2-wrapper').hide(); 
                        $('#hinh-thuc-thanh-toan-wrapper').hide(); 
                        $('#lphs').text('Thông tin hồ sơ '+'<?php echo $hoSoOnline->getSoHoSo(); ?>');
                    }
                });
            </script>
        </section>
        <section id="le-phi-nop-2-wrapper" style="margin:30px 0 0 0;display: none">
            <h4 style="color:#0fbd85"><i class="fa fa-truck"></i> Dự kiến <?php echo strtolower($lb_lephi);?> thanh toán cho bưu điện <small>Đơn vị tính: <strong>VNĐ</strong></small></h4>
            <p>Bảng kê dự kiến các khoản <?php echo strtolower($lb_lephi);?> mà cá nhân hoặc tổ chức phải trả trực tiếp cho bưu điện. <a href="https://hcconline.vnpost.vn/PriceHCC/Muc_Cuoc_HCC.html" target="_blank">(Xem quy định giá cước)</a></p>
            <div class="panel panel-default">
                <table class="table">
                    <colgroup>
                        <col width="35%"/>
                        <col width="25%"/>
                        <col width="5%"/>
                    </colgroup>
                    <tbody id="tbody-2"></tbody>
                    <tfoot>
                        <tr id="tong-cong-2">
                            <td><strong>Tổng cộng</strong></td>
                            <td class="amount total">0</td>
                            <td class="unit"><strong>VNĐ</strong></td>
                            <td class="desc"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <?php if((int) Entity\System\Parameter::fromId('DOI_TUONG_GIAM_CUOC_VNPOST')->getValue() === 1): //IGATESUPP-26470 tttruong-kv1
                $dt = $model->db->ExecuteCursor("BEGIN LCI_HOME.DM_DT_GIAM_PHI_VNPOST(:P_CUR);END;", 'P_CUR');
            ?>
            <div class="row">
                <div class="col-md-5">
                    <p class="" style="font-weight: 600;line-height: 34px;">Nếu là đối tượng được miễn giảm cước vui lòng chọn ở đây: </p>
                </div>
                <div class="col-md-4">
                    <select name="loaidt_vnpost" id="chonDoiTuong" class="form-control">
                        <?php foreach ($dt as $item ) : ?>
                            <option value="<?php echo $item['MA_DM_DT_GIAM_PHI'] ?>" <?php echo ($item['MA_DM_DT_GIAM_PHI'] == 'dt0') ? 'selected' : '' ?>
                                    data-discount="<?php echo $item['PHAN_TRAM_MIEN_GIAM'] ?>"> <?php echo $item['TEN_DM_DT_GIAM_PHI'] ?></option>
                        <?php endforeach;?>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-5">
                    <p class="" style="font-weight: 600;line-height: 34px;margin: 0;">Tổng tiền phải thanh toán cho bưu điện là:</p>
                </div>
                <div class="col-md-4">
                    <input type="hidden" value="" name="txt_tienDoiTuongGiam" id="txt_tienDoiTuongGiam">
                    <p id="tongTien" style="font-weight: 600;line-height: 34px;margin: 0;"></p>
                </div>
            </div>
            <?php endif;?>
            <?php if($comment = trim(Entity\System\Parameter::fromId('dvctt_buoclephihs_slpttcqgqcmt')->getValue())): ?>
            <div><?php echo $comment; ?></div>
            <?php endif; ?>
        </section>
        <?php if((int) Entity\System\Parameter::fromId('dvctt_buoclephihs_ttlptt')->getValue() === 1): ?>
        <section id="hinh-thuc-thanh-toan-wrapper" style="margin:30px 0 0 0">
            <h4 style="color:#0fbd12"><i class="fa fa-credit-card"></i> Thanh toán <?php echo strtolower($lb_lephi);?> cho cơ quan giải quyết</h4>
            <?php if($thuTuc->getMaMucDo() === 'MUC_DO_4' && (empty($BDH_AN_THANH_TOAN_TRUC_TUYEN) || $BDH_AN_THANH_TOAN_TRUC_TUYEN == '0')): ?>
            <p class="description">Các khoản <?php echo strtolower($lb_lephi);?> thanh toán phải có giá trị và <strong class="text-warning">không bao gồm các khoản <?php echo strtolower($lb_lephi);?> bưu điện</strong>.</p>
            <div class="form-wrapper" id="hinh-thuc-thanh-toan-form">
                <p><i class="fa fa-spin fa-spinner"></i> Loading...</p>
            </div>
            <div id="thong-tin-thanh-toan-wrapper" style="display:none;"></div>
            <script type="text/javascript">
                require(['NthLib', 'hoSoOnline'], function(Nth, hoSoOnline) {
                    var cnp = 'HoSoOnline_';
                    var $ttttWrapper = $('#thong-tin-thanh-toan-wrapper');
                    var fieldset = new Nth.FormBuilder.Fieldset('fs-phuong-thuc-thanh-toan', {
                        hide: __current.layTongLePhi() === 0,
                        controlNamePrefix: cnp
                    });
                    var row_1 = new Nth.FormBuilder.Row('row-1', {
                        parentComponent: fieldset,
                        customizable: false
                    });
                    var row_2 = new Nth.FormBuilder.Row('row-2', {
                        parentComponent: fieldset,
                        customizable: false
                    });
                    var col_1 = new Nth.FormBuilder.Column('column-1', {
                        parentComponent: row_1,
                        customizable: false
                    });
                    var col_2 = new Nth.FormBuilder.Column('column-2', {
                        parentComponent: row_1
                    });
                    var col_3 = new Nth.FormBuilder.Column('column-3', {
                        parentComponent: row_1
                    });
                    var col_4 = new Nth.FormBuilder.Column('column-4', {
                        parentComponent: row_2
                    });
                    var col_5 = new Nth.FormBuilder.Column('column-5', {
                        parentComponent: row_2
                    });
                    var col_6 = new Nth.FormBuilder.Column('column-6', {
                        parentComponent: row_2
                    });
                    var col_7 = new Nth.FormBuilder.Column('column-7', {
                        parentComponent: row_2,
                        hide: true,
                        wrapperAttributes: {
                            'class': 'col-xs-8'
                        }
                    });
                    var col_8 = new Nth.FormBuilder.Column('column-8', {
                        parentComponent: row_2,
                        wrapperAttributes: {
                            'class': 'col-xs-4'
                        }
                    });

                    
                    var dsHinhThucThanhToan = JSON.parse(hoSoOnline.qttt.thuTuc.dsHinhThucThanhToan);
                    var maHinhThucThanhToan = new Nth.FormBuilder.Element.Select('maHinhThucThanhToan', {
                        label: 'Phương thức thanh toán',
                        parentComponent: col_1,
                        selectItemUrl: 'model/htmloption/DM_HINH_THUC_THANH_TOAN',
                        required: 1,
                        value: hoSoOnline.maHinhThucThanhToan,
                        autoInit: false,
                        customizable: false,
                        onvalid: function () {
                            hoSoOnline.maHinhThucThanhToan = this.getValue();
                            hoSoOnline.hinhThucThanhToan.maHinhThuc = hoSoOnline.maHinhThucThanhToan;
                            hoSoOnline.hinhThucThanhToan.tenHinhThuc = this.getSelectedText();
                        },
                        selectItemRequestData : {'P_TRANG_THAI' : 1, 'P_MA_HINH_THUC_ALLOW' : dsHinhThucThanhToan}
                    });
                    maHinhThucThanhToan.getWrapper().getNode().on('nth.fb.bound.httt', function() {
                        maHinhThucThanhToan.trigger('initialize');
                        __current.tinhCuocPhiThuGom();
                    });
                    maHinhThucThanhToan.getWrapper().getNode().on('initialize', function() {
                        $ttttWrapper.hide();
                        ;(function () { //Kiem tra doi voi truong hop thu ho
                            var option = maHinhThucThanhToan.getControl().getNode().find('option[value=6]');
                            var fs = __current.getFieldset('fs-hinh-thuc-nop');
                            var e = fs ? fs.findElement('maHinhThucNop') : null;
                            var maHinhThucNop = e ? parseInt(e.getValue()) : 0;
                            if (maHinhThucNop === 1) {
                                option.removeAttr('disabled');
                            } else {
                                option.attr('disabled', true);
                            }
                        })();
                        var value = parseInt(maHinhThucThanhToan.getValue());
                        $.each(fieldset.getComponents(), function (i, c) {
                            var customizable = c.getOption('customizable', true);
                            if (customizable) {
                                c.hide();
                            }
                            if (c instanceof Nth.FormBuilder.Element && customizable) {
                                c.setRequired(false);
                            }
                        });
                        // remove le phi thanh toan
                        if (!isNaN(value)){
                            __current.removeLePhiHoSo({mucLePhi: 5500, thanhToanCho: 1, maLoaiLePhi: 4, loaiLePhi: {maLoai: 4, tenLoai: 'Cước thanh toán trực tuyến VNPay'}}, true);
                            __current.removeLePhiHoSo({mucLePhi: 5500, thanhToanCho: 1, maLoaiLePhi: 7, loaiLePhi: {maLoai: 7, tenLoai: 'Cước thanh toán trực tuyến Vietinbank'}}, true);
                            __current.removeLePhiHoSo({mucLePhi: 5500, thanhToanCho: 1, maLoaiLePhi: 8, loaiLePhi: {maLoai: 8, tenLoai: 'Cước thanh toán thu hộ lệ phí bưu điện'}}, true);
                        }
                        
                        //Nhi thêm cho phép đính kèm file chứng thực thanh toán lệ phí IGATESUPP-8729
                        var fileDaThanhToan = fieldset.getComponent('fileDaThanhToan');
                        fileDaThanhToan =  new Nth.FormBuilder.Element.File('fileDaThanhToan', {
                            parentComponent: col_8,
                            label: 'Hoá đơn đã thanh toán',
                            value: hoSoOnline.fileDaThanhToan,
                            asyncUpload: true,
                            controlAttributes: {
                                multiple: true
                            },
                            hide: false,
                            uploadURL: function() {
                                return '<?php echo SITE_ROOT ?>' + 'hethong/filemanager/upload_file?postname='+cnp+'fileDaThanhToan';
                            },
                            removeURL: 'hethong/filemanager/remove_file'
                        });
                       
                        fieldset.addComponent(fileDaThanhToan);
                        //end Nhi
                        if (value === 1) {//thanh toan qua buu dien
                            var buuCucThanhToan_phuongXa_quanHuyen_maTinhThanh = fieldset.getComponent('buuCucThanhToan_phuongXa_quanHuyen_maTinhThanh');
                            var buuCucThanhToan_phuongXa_maQuanHuyen = fieldset.getComponent('buuCucThanhToan_phuongXa_maQuanHuyen');
                            var buuCucThanhToan_maPhuongXa = fieldset.getComponent('buuCucThanhToan_maPhuongXa');
                            var maBuuCucThanhToan = fieldset.getComponent('maBuuCucThanhToan');
                            var soHoaDonThanhToan = fieldset.getComponent('soHoaDonThanhToan');
                            if (!buuCucThanhToan_phuongXa_quanHuyen_maTinhThanh) {
                                buuCucThanhToan_phuongXa_quanHuyen_maTinhThanh = new Nth.FormBuilder.Element.Select('buuCucThanhToan_phuongXa_quanHuyen_maTinhThanh', {
                                    label: 'Tỉnh/TP thanh toán',
                                    parentComponent: col_2,
                                    bindName: 'P_MA_TINH_THANH',
                                    selectItemUrl: 'model/htmloption/DM_TINH_THANH',
                                    value: hoSoOnline.buuCucThanhToan.phuongXa.quanHuyen.maTinhThanh || MA_TINH_THANH,
                                    hide: true,
                                    onvalid: function () {
                                        hoSoOnline.buuCucThanhToan.phuongXa.quanHuyen.maTinhThanh = this.getValue();
                                        hoSoOnline.buuCucThanhToan.phuongXa.quanHuyen.tinhThanh.maTinhThanh = hoSoOnline.buuCucThanhToan.phuongXa.quanHuyen.maTinhThanh;
                                        hoSoOnline.buuCucThanhToan.phuongXa.quanHuyen.tinhThanh.tenTinhThanh = this.getSelectedText();
                                    }
                                });
                                fieldset.addComponent(buuCucThanhToan_phuongXa_quanHuyen_maTinhThanh);
                            }
                            if (!buuCucThanhToan_phuongXa_maQuanHuyen) {
                                buuCucThanhToan_phuongXa_maQuanHuyen = new Nth.FormBuilder.Element.Select('buuCucThanhToan_phuongXa_maQuanHuyen', {
                                    label: 'Quận/Huyện thanh toán',
                                    parentComponent: col_3,
                                    addingMethod: 'prepend',
                                    bindName: 'P_MA_QUAN_HUYEN',
                                    selectItemUrl: 'model/htmloption/DM_QUAN_HUYEN',
                                    value: hoSoOnline.buuCucThanhToan.phuongXa.maQuanHuyen,
                                    hide: true, 
                                    bindBy: buuCucThanhToan_phuongXa_quanHuyen_maTinhThanh,
                                    onvalid: function () {
                                        hoSoOnline.buuCucThanhToan.phuongXa.maQuanHuyen = this.getValue();
                                        hoSoOnline.buuCucThanhToan.phuongXa.quanHuyen.maQuanHuyen = hoSoOnline.buuCucThanhToan.phuongXa.maQuanHuyen;
                                        hoSoOnline.buuCucThanhToan.phuongXa.quanHuyen.tenQuanHuyen = this.getSelectedText();
                                    }
                                });
                                fieldset.addComponent(buuCucThanhToan_phuongXa_maQuanHuyen);
                            }
                            if (!buuCucThanhToan_maPhuongXa) {
                                buuCucThanhToan_maPhuongXa = new Nth.FormBuilder.Element.Select('buuCucThanhToan_maPhuongXa', {
                                    label: 'Phường/Xã/Thị trấn thanh toán',
                                    parentComponent: col_4,
                                    bindName: 'P_MA_PHUONG_XA',
                                    selectItemUrl: 'model/htmloption/DM_PHUONG_XA',
                                    value: hoSoOnline.buuCucThanhToan.maPhuongXa,
                                    hide: true,
                                    bindBy: [buuCucThanhToan_phuongXa_quanHuyen_maTinhThanh, buuCucThanhToan_phuongXa_maQuanHuyen],
                                    onvalid: function () {
                                        hoSoOnline.buuCucThanhToan.maPhuongXa = this.getValue();
                                        hoSoOnline.buuCucThanhToan.phuongXa.maPhuongXa = hoSoOnline.buuCucThanhToan.maPhuongXa;
                                        hoSoOnline.buuCucThanhToan.phuongXa.tenPhuongXa = this.getSelectedText();
                                    }
                                });
                                fieldset.addComponent(buuCucThanhToan_maPhuongXa);
                            }
                            if (!maBuuCucThanhToan) {
                                maBuuCucThanhToan = new Nth.FormBuilder.Element.Select('maBuuCucThanhToan', {
                                    label: 'Bưu điện thanh toán',
                                    parentComponent: col_5,
                                    bindName: 'P_MA_PHUONG_XA',
                                    selectItemUrl: 'model/htmloption/DM_BUU_DIEN',
                                    value: hoSoOnline.maBuuCucThanhToan,
                                    hide: true,
                                    bindBy: buuCucThanhToan_maPhuongXa,
                                    onvalid: function () {
                                        hoSoOnline.maBuuCucThanhToan = this.getValue();
                                        hoSoOnline.buuCucThanhToan.maBuuCucThanhToan = hoSoOnline.maBuuCucThanhToan;
                                        hoSoOnline.buuCucThanhToan.tenBuuCuc = this.getSelectedText();
                                    }
                                });
                                fieldset.addComponent(maBuuCucThanhToan);
                            }
                            if (!soHoaDonThanhToan) {
                                soHoaDonThanhToan = new Nth.FormBuilder.Element.Text('soHoaDonThanhToan', {
                                    parentComponent: col_6,
                                    label: 'Số hóa đơn',
                                    value: hoSoOnline.soHoaDonThanhToan,
                                    hide: true,
                                    onvalid: function () {
                                        hoSoOnline.soHoaDonThanhToan = this.getValue();
                                    }
                                });
                                fieldset.addComponent(soHoaDonThanhToan);
                            }
                            col_8.show();
                            $('#fileDaThanhToan').removeAttr('style');  
                            col_2.show();
                            col_3.show();
                            col_4.show();
                            col_5.show();
                            col_6.show();
                            buuCucThanhToan_phuongXa_quanHuyen_maTinhThanh.setRequired(true).show();
                            buuCucThanhToan_phuongXa_maQuanHuyen.setRequired(true).show();
                            buuCucThanhToan_maPhuongXa.setRequired(true).show();
                            maBuuCucThanhToan.setRequired(true).show();
                            soHoaDonThanhToan.show();
                        } else if (value === 2) {//Thanh toan qua ngan hang
                            col_8.show();
                            var batBuocUploadFileThanhToan = '<?php echo $batBuocUploadFileThanhToan ?>';
                            if (batBuocUploadFileThanhToan=='1') {
                                var fileDaThanhToan = fieldset.getComponent('fileDaThanhToan');
                                fileDaThanhToan.setRequired(true);
                                fileDaThanhToan.show();
                            }else{
                                $('#fileDaThanhToan').removeAttr('style'); 
                            }

                            var CNNganHangThanhToan_maNganHang = fieldset.getComponent('CNNganHangThanhToan_maNganHang');
                            var CNNganHangThanhToan_phuongXa_quanHuyen_maTinhThanh = fieldset.getComponent('CNNganHangThanhToan_phuongXa_quanHuyen_maTinhThanh');
                            var CNNganHangThanhToan_phuongXa_maQuanHuyen = fieldset.getComponent('CNNganHangThanhToan_phuongXa_maQuanHuyen');
                            var maCNNganHangThanhToan = fieldset.getComponent('maCNNganHangThanhToan');
                            var soHoaDonThanhToan = fieldset.getComponent('soHoaDonThanhToan');
                            if (!CNNganHangThanhToan_maNganHang) {
                                CNNganHangThanhToan_maNganHang = new Nth.FormBuilder.Element.Select('CNNganHangThanhToan_maNganHang', {
                                    label: 'Ngân hàng thanh toán',
                                    parentComponent: col_2,
                                    bindName: 'maNganHang',
                                    selectItemUrl: 'model/htmloption/DM_NGAN_HANG',
                                    value: hoSoOnline.CNNganHangThanhToan.nganHang.maNganHang,
                                    hide: true,
                                    onvalid: function () {
                                        hoSoOnline.CNNganHangThanhToan.nganHang.maNganHang = this.getValue();
                                        hoSoOnline.CNNganHangThanhToan.nganHang.tenNganHang = this.getSelectedText();
                                    }
                                });
                                fieldset.addComponent(CNNganHangThanhToan_maNganHang);
                            }
                            if (!CNNganHangThanhToan_phuongXa_quanHuyen_maTinhThanh) {
                                CNNganHangThanhToan_phuongXa_quanHuyen_maTinhThanh = new Nth.FormBuilder.Element.Select('CNNganHangThanhToan_phuongXa_quanHuyen_maTinhThanh', {
                                    label: 'Tỉnh/TP thanh toán',
                                    parentComponent: col_3,
                                    addingMethod: 'prepend',
                                    bindName: 'P_MA_TINH_THANH',
                                    selectItemUrl: 'model/htmloption/DM_TINH_THANH',
                                    value: hoSoOnline.CNNganHangThanhToan.phuongXa.quanHuyen.maTinhThanh || MA_TINH_THANH,
                                    hide: true,
                                    onvalid: function () {
                                        hoSoOnline.CNNganHangThanhToan.phuongXa.quanHuyen.maTinhThanh = this.getValue();
                                        hoSoOnline.CNNganHangThanhToan.phuongXa.quanHuyen.tinhThanh.maTinhThanh = hoSoOnline.CNNganHangThanhToan.phuongXa.quanHuyen.maTinhThanh;
                                        hoSoOnline.CNNganHangThanhToan.phuongXa.quanHuyen.tinhThanh.tenTinhThanh = this.getSelectedText();
                                    }
                                });
                                fieldset.addComponent(CNNganHangThanhToan_phuongXa_quanHuyen_maTinhThanh);
                            }
                            if (!CNNganHangThanhToan_phuongXa_maQuanHuyen) {
                                CNNganHangThanhToan_phuongXa_maQuanHuyen = new Nth.FormBuilder.Element.Select('CNNganHangThanhToan_phuongXa_maQuanHuyen', {
                                    label: 'Quận/Huyện thanh toán',
                                    parentComponent: col_4,
                                    bindName: 'maQuanHuyen',
                                    selectItemUrl: 'model/htmloption/DM_QUAN_HUYEN',
                                    value: hoSoOnline.CNNganHangThanhToan.phuongXa.maQuanHuyen,
                                    hide: true,
                                    bindBy: CNNganHangThanhToan_phuongXa_quanHuyen_maTinhThanh,
                                    onvalid: function () {
                                        hoSoOnline.CNNganHangThanhToan.phuongXa.maQuanHuyen = this.getValue();
                                        hoSoOnline.CNNganHangThanhToan.phuongXa.quanHuyen.maQuanHuyen = hoSoOnline.CNNganHangThanhToan.phuongXa.maQuanHuyen;
                                        hoSoOnline.CNNganHangThanhToan.phuongXa.quanHuyen.tenQuanHuyen = this.getSelectedText();
                                    }
                                });
                                fieldset.addComponent(CNNganHangThanhToan_phuongXa_maQuanHuyen);
                            }
                            if (!maCNNganHangThanhToan) {
                                maCNNganHangThanhToan = new Nth.FormBuilder.Element.Select('maCNNganHangThanhToan', {
                                    label: 'Chi nhánh ngân hàng thanh toán',
                                    parentComponent: col_5,
                                    bindName: 'maChiNhanh',
                                    selectItemUrl: 'public/selectitems/chi-nhanh-ngan-hang',
                                    value: hoSoOnline.maCNNganHangThanhToan,
                                    hide: true,
                                    bindConstraint: 0,
                                    bindBy: [CNNganHangThanhToan_maNganHang, CNNganHangThanhToan_phuongXa_maQuanHuyen],
                                    onvalid: function () {
                                        hoSoOnline.maCNNganHangThanhToan = this.getValue();
                                        hoSoOnline.CNNganHangThanhToan.maChiNhanh = hoSoOnline.maCNNganHangThanhToan;
                                        hoSoOnline.CNNganHangThanhToan.tenChiNhanh = this.getSelectedText();
                                        hoSoOnline.CNNganHangThanhToan.maPhuongXa = this.getSelectedItem().data('ma-phuong-xa');
                                        hoSoOnline.CNNganHangThanhToan.phuongXa.maPhuongXa = hoSoOnline.CNNganHangThanhToan.maPhuongXa;
                                        hoSoOnline.CNNganHangThanhToan.phuongXa.tenPhuongXa = this.getSelectedItem().data('ten-phuong-xa');
                                    }
                                });
                                fieldset.addComponent(maCNNganHangThanhToan);
                            }
                            if (!soHoaDonThanhToan) {
                                soHoaDonThanhToan = new Nth.FormBuilder.Element.Text('soHoaDonThanhToan', {
                                    parentComponent: col_6,
                                    label: 'Số hóa đơn',
                                    value: hoSoOnline.soHoaDonThanhToan,
                                    hide: true,
                                    onvalid: function () {
                                        hoSoOnline.soHoaDonThanhToan = this.getValue();
                                    }
                                });
                                fieldset.addComponent(soHoaDonThanhToan);
                            }
                            col_2.show();
                            col_3.show();
                            col_4.show();
                            col_5.show();
                            col_6.show();
                            CNNganHangThanhToan_maNganHang.setRequired(false).show();
                            CNNganHangThanhToan_phuongXa_quanHuyen_maTinhThanh.setRequired(true).show();
                            CNNganHangThanhToan_phuongXa_maQuanHuyen.setRequired(true).show();
                            maCNNganHangThanhToan.setRequired(false).show();
                            soHoaDonThanhToan.show();
                           
                        } else if (value === 8) {//Thanh toan qua vnpay
                            col_8.show();
                            $('#fileDaThanhToan').removeAttr('style');  
                            var lePhiThanhToan = '<?php echo \Model\Entity\HinhThucThanhToan::fromMaHinhThuc(8)->getPhiThanhToan(); ?>';
                            if (lePhiThanhToan > 0) {
                                __current.editLePhiHoSo({mucLePhi: lePhiThanhToan, thanhToanCho: 1, maLoaiLePhi: 4, soLuong: 1, batBuocThanhToan: 1, loaiLePhi: {maLoai: 4, tenLoai: 'Cước thanh toán trực tuyến VNPay'}}, '', true);
                                __current.hienThiTongLePhi();
                            }
                        } else if (value === 9) {//Thanh toan qua vietinbank
                            col_8.show();
                            $('#fileDaThanhToan').removeAttr('style');  
                            var lePhiThanhToan = '<?php echo \Model\Entity\HinhThucThanhToan::fromMaHinhThuc(9)->getPhiThanhToan(); ?>';
                            if (lePhiThanhToan > 0) {
                                __current.editLePhiHoSo({mucLePhi: lePhiThanhToan, thanhToanCho: 1, maLoaiLePhi: 7, soLuong: 1, batBuocThanhToan: 1, loaiLePhi: {maLoai: 7, tenLoai: 'Cước thanh toán trực tuyến Vietinbank'}}, '', true);
                                __current.hienThiTongLePhi();
                            }
                        } else if (value === 5) {//Thanh toan truc tuyen su dung cong thanh toan VNPT SmartGate
                            //...
                        } else if (value === 6) {//Thanh toan bang hinh thuc su dung dich vu thu ho cua buu dien
                            __current.tinhCuocPhiThuHo();
                            __current.hienThiTongLePhi();
                        } else {

                        }
                    });

                    fieldset.addComponent(row_1, row_2, col_1, col_2, col_3, col_4, col_5, col_6, col_7, col_8, maHinhThucThanhToan.init());
                    fieldset.setOption('onHienThiTongLePhi', function (total) {
                        if (total > 0) {
                            this.show();
                            maHinhThucThanhToan.setRequired(true);
                        } else {
                            this.hide();
                            maHinhThucThanhToan.setRequired(false);
                            maHinhThucThanhToan.reset().trigger('initialize');
                        }
                    });
                    fieldset.getOption('onHienThiTongLePhi').call(fieldset, __current.layTongLePhi({thanhToanCho: 1}));
                    $('#hinh-thuc-thanh-toan-form').html(fieldset.getDomNode());
                    __current.fs.push(fieldset);
                });
            </script>
            <?php else: ?>
            <p>Cá nhân hoặc tổ chức vui lòng thanh toán các khoản <?php echo strtolower($lb_lephi);?> trên tại cơ quan giải quyết.</p>
            <?php endif; ?>
        </section>
        <?php endif; ?>
    </form>
    <section id="page-action-wrapper" style="margin: 50px 0 50px 0">
        <div class="row">
            <div class="col-xs-6">
                <a class="btn btn-default" id="btnBack" href="<?php echo $progress->getPrevStep()->getLink() ?>"><i class="fa fa-arrow-left"></i> Quay lại</a>
            </div>
            <div class="col-xs-6 text-right">
                <?php if($hoSoOnline->duocPhepHuy()): ?>
                <button id="btn-huy-ho-so" class="btn btn-danger" disabled="true"><i class="fa fa-trash"></i> Hủy hồ sơ</button>
                <?php endif; ?>
                <button type="button" class="btn btn-primary" disabled="true" id="btn-next"><i class="fa fa-arrow-right"></i> Đồng ý và tiếp tục</button>
            </div>
        </div>
    </section>
    <script type="text/javascript">
        require(['NthLib', 'queryData', 'hoSoOnline'], function (Nth, queryData, hoSoOnline) {
            $('#btn-huy-ho-so').removeAttr('disabled').on('click', function () {
                Nth.Confirm('Có chắc bạn muốn hủy hồ sơ này?', function (choosed) {
                    if (Nth.Confirm.OK === choosed) {
                        window.location.href = SITE_ROOT + 'bo-cong-an/tiep-nhan-online/huy-ho-so?sid=' + queryData.sid + '&token=' + '<?php echo $token ?>';
                    }
                });                
            });
            var diDong = '<?php echo $congDan->getDiDong()?>'
            $('#btn-next').removeAttr('disabled').on('click', function () {
                var dungVnpostNop = $('#_fcmaHinhThucNop').val();
                var dungVnpostNhan = $('#_fcmaHinhThucNhanKetQua').val();
                var hinhThucThanhToan = $('#_fcmaHinhThucThanhToan').val();
                var taiKhoanCongDan = '<?php echo count($duLieuCongDan) ?>';
                var laTaiKhoanDoanhNghiep = '<?php echo $laTaiKhoanDoanhNghiep ?>';
                var trangThaiTaiKhoanNganHang = '<?php echo $trangThaiTaiKhoanNganHang ?>';
                if((dungVnpostNop == 1 || dungVnpostNhan == 1) && diDong == 0 ){
                    alert("Thủ tục có kết nối bưu điện, vui lòng nhập Số điện thoại ở bước 2!");
                    exit();
                }
                if (hinhThucThanhToan == 9) {
                    if (taiKhoanCongDan == 0) {
                        alert("Bạn phải đăng nhập tài khoản công dân thì mới thanh toán Vietinbank được!");
                        exit();
                    } else if (laTaiKhoanDoanhNghiep == 0) {
                        alert('Tài khoản công dân không thuộc loại tài khoản doanh nghiệp, không thể thanh toán qua Vietinbank được!');
                        exit();
                    } else if (trangThaiTaiKhoanNganHang != 2) {
                        alert('Tài khoản ngân hàng chưa được xác nhận, không thể thanh toán qua Vietinbank được');
                        exit();
                    }
                }
                if (__current.message) {
                    return Nth.Alert(__current.message);
                }
                var atm = new Nth.AsyncTaskManager({
                    auto: false,
                    finished: function () {
                        __current.hiddenData['HoSoOnline_JSON_PROPERTIES'] = JSON.stringify(hoSoOnline);
                        __current.createHiddenInputs();
                        __current.goNext();
                    }
                });
                var showMessage = function (message, fs) {
                    var element = this;
                    return Nth.Alert(message, function () {
                        fs.getOption('focus', function (fn) {
                            fn.call(this);
                        }).call(fs, function () {
                            setTimeout(function () { element.showMessage() }, 500);
                        });
                    });
                }
                $.each(__current.fs, function (i, fs) {
                    atm.add(fs, fs.isValid, [
                        {
                            passed: function () {
                                this.getOption('onvalid', function () {}).call(this);
                            },
                            finished: function () {
                                this.getOption('onvalid', function () {}).call(this);
                            },
                            done: function () {
                                var message = this.getMessage();
                                if (message && fs.getOption('required', true)) {
                                    var element = this;
                                    if (typeof message === 'string' || typeof message === 'number') {
                                        return showMessage.call(element, message, fs);
                                    }
                                    if ($.isPlainObject(message) && message.xhr) {
                                        var contentType = message.xhr.getResponseHeader('content-type');
                                        if (contentType === 'text/plain') {
                                            return showMessage.call(element, message.xhr.responseText, fs);
                                        }
                                        if (contentType === 'text/html') {
                                            var $container = $('#html-message-container');
                                            if ($container.length === 0) {
                                                $container = $('<div/>', {id: 'html-message-container'}).appendTo($('body'));
                                            }
                                            return $container.html(message.xhr.responseText);
                                        }
                                    }
                                    return showMessage.call(element, 'Unknown error', fs);
                                }
                                atm.done().next();
                            }
                        }
                    ]);   
                });
                atm.execute();
            });
        });
    </script>
    <script>
        var link_was_clicked = false;
        document.addEventListener("click", function(e) {
            if (e.target.nodeName.toLowerCase() === 'a' && e.target.id !== 'btnBack') {
                link_was_clicked = true;
            } else {
                link_was_clicked = false;
            }
        }, true);
        window.onbeforeunload = function(e) {
            if(link_was_clicked) {
                message = "Bạn có muốn ngừng nộp hồ sơ ?";
                e.returnValue = message;
                return message;
            }
        }
    </script>
</div>

<?php
// Hienctt KV1: check riêng header / footer cho BCA
if ($isAGTemplate) {
    $this->template->display('angiang/frontend.footer.php');
} else if($BCA_SERVICE_ACTIVE == 1) {
    $this->template->display('bocongan/frontend.optimize-footer.php');
} else if($HNI_TEMPLATE_ACTIVE){
    $js = SITE_ROOT . 'apps/dichvucong/resources/js/js_hanoi.js';
    echo "<script src='" . $js . "' type='text/javascript'></script>";
    $this->template->display('hanoi/frontend.footer.php');
} else if ($isVPC) {
    $this->template->display('vinhphuc/frontend.optimize-footer.php');
}
else{
    $this->template->display('dichvucong/frontend.optimize-footer.php');
}
?>
