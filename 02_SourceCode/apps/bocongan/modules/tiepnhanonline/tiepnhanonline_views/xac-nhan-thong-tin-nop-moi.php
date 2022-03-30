<?php

use Model\Entity;
use Nth\File\File;
use Nth\File\Files;
use Nth\Html\Node;
use Nth\Helper\Number;
use Oracle\Package;

File::setUriDownload(SITE_ROOT);

$this->template->title = $progress->getActiveStep()->getDescription();
// Hienctt KV1: check riêng header / footer cho BCA
$this->template->display('bocongan/frontend.optimize-header.php');

$NAME_STEP_DVC_NOP_HS_ONLINE = Model\Entity\System\Parameter::fromId('NAME_STEP_DVC_NOP_HS_ONLINE')->getValue();

$csrf = new Zend\Validator\Csrf();
$token = $csrf->getHash();
$tongLePhiChuaThanhToan = $dmLePhiHoSo->tinhLePhiChuaThanhToan(['thanhToanCho' => 1, 'batBuocThanhToan' => 1, 'daThanhToan' => 0]);
$changeConfigFile = Entity\System\Parameter::fromId('HNI_CHANGE_CONFIG_FILE')->getValue();

$type_element = getThamSoArray(Entity\System\Parameter::fromId("ID_EFORM_ELEMENT_TYPE")->getValue());
$type_table = '';
$type_checkbox = '';
if(!empty($type_element)) {
    $type_table = !empty($type_element['Table']) ? $type_element['Table'] : 4;
    $type_checkbox = !empty($type_element['Checkbox']) ? $type_element['Checkbox'] : 13;
}
?>
    <style type="text/css">
        .label-fill-out, .label-fill-out-sm{font-size: 11px;position: relative;top:-8px;margin-right: 5px;}
        .label-fill-out-sm{font-size: 9px;top:-5px;}
        section .amount{text-align: right}
        section .amount.total{font-weight: bold;}
        section .paid .name, section .paid .amount, section .paid .unit{text-decoration: line-through}
        section .paid .desc{color:#13a20a}
        #thanh-phan-ho-so-wrapper .panel-title{font-size: 14px;}
        .lr-ctrl {
            padding-right: 50px;
            position: relative;
            display: inline-block;
            width: 100%;
            height: 34px
        }

        .form-wrapper .form-group.listfile ul {
            list-style: none;
        }
        .display-dropdown-list ul {
            position: absolute !important;
        }

        .display-dropdown-list div:hover {
            z-index: 2;
        }
        #thanh-phan-ho-so-wrapper .wrapper-content-bieu-mau-giay-to {
            pointer-events: none !important;
        }
    </style>

    <script type="text/javascript">
        require(['nth.socket.io', 'app'], function () {
            $('.selectpicker').selectpicker();
        });
    </script>
    <script type="text/javascript">
        var __current = { fs: [], hiddenData: {} }
        define('NthLib', [
            'Nth/Nth',
            'Nth/Alert',
            'Nth/Confirm',
            'Nth/Helper/Html',
            'Nth/FormBuilder',
            'Nth/FormBuilder/Convertor',
            'Nth/FormBuilder/Fieldset',
            'Nth/FormBuilder/Element/ListFile'
        ], function (Nth) {
            Nth.FormBuilder.__getInstance().setOption('siteRoot', SITE_ROOT);
            return Nth;
        });
        define('queryData', function () {
            return JSON.parse('<?php echo addslashes(json_encode($queryData)) ?>');
        });
        require(['NthLib'], function () {
            __current.createHiddenInputs = function () {
                var $wrapper = $('#mainForm').html(null);
                $.each(this.hiddenData, function (name, value) {
                    $('<input/>', {id: name, type: 'hidden', name: name, value: value}).prependTo($wrapper);
                });
            }
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
                                <div class="number">2</div><?php echo $NAME_STEP_DVC_NOP_HS_ONLINE?:'Lựa chọn DVC'; ?></div>
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
        <form id="mainForm" action="" method="POST"></form>
        <section id="page-header-wrapper">
            <h3 class="page-header">Nộp hồ sơ <?php echo $hoSoOnline->getSoHoSo() ?></h3>
            <div id="nav-wrapper">
                <?php // echo $progress->toHtml(); ?>
            </div>
            <p><?php echo $progress->getActiveStep()->getApplyNotes() ?></p>
        </section>
        <section id="thu-tuc-da-chon-wrapper" style="margin: 30px 0 0 0">
            <h4 style="margin-bottom:15px">
                <span class="<?php echo $mucDo->getBootstrapCss(); ?> label-fill-out"><?php echo $mucDo->getTenMucDo(); ?></span>
                <?php echo $thuTuc->getTenTat() ?> - <?php echo $thuTuc->getTenThuTuc() ?>
            </h4>
        </section>

        <section>
            <div id="form-content">
                <i class="fa fa-refresh"></i> Loading...
            </div>
            <script>
                define('data', function () {
                    return JSON.parse('<?php echo addslashes(json_encode($congDan->toArray())) ?>');
                });
                define('serve', function () {
                    return function (fieldset) {
                        $('#form-content').html(fieldset.getDomNode());
                    }
                });
            </script>
            <script type="text/javascript">
                define('hoSoOnline', function () {
                    return JSON.parse('<?php echo addslashes(json_encode($hoSoOnline->toArray())) ?>');
                });
                require(['NthLib', 'data','hoSoOnline', 'serve', 'queryData', 'submodal'], function(Nth, data,hoSoOnline, serve, queryData) {
                    var bm = JSON.parse('<?php echo addslashes(json_encode($hoSoOnline->layBieuMauNguoiNop()->toArray())) ?>');
                    console.log(bm.javascriptEmbedSafely)

                    var cnp = 'CongDan_';
                    var fieldset = Nth.FormBuilder.Fieldset.__fromXml(bm.xmlBieuMau, {
                        controlNamePrefix: cnp,
                        controlNamePrefixAlways: true,
                        autoInit: false
                    });
                    if (bm.javascriptEmbedSafely) {
                        console.log('bm.javascriptEmbedSafel')
                        console.log(fieldset.getName())
                        $('body').append($('<script/>', {
                            type: 'text/javascript'
                        }).html(';(function (name) {var fieldset = Nth.FormBuilder.findFieldset(name);' + bm.javascriptEmbedSafely + '})("' + fieldset.getName()+ '");'));
                    }
                    var datas2 = JSON.parse('<?php echo addslashes(json_encode($controlName)); ?>');
                    var datas2Val = JSON.parse('<?php echo addslashes(json_encode($giaTri)); ?>');
                    var datasAll = JSON.parse('<?php echo addslashes(json_encode($giaTriBML)); ?>');
                    var type_checkbox = '<?php echo $type_checkbox; ?>';
                    var type_table = '<?php echo $type_table; ?>';

                    var components = fieldset.groupComponentsByName();
                    //Nhi cấu hình động 14/3/2019 IGATESUPP-8128
                    if(datas2 != null && datas2Val != null){
                        datas2.forEach(function(item,index,arr) {
                            var tx = fieldset.getComponent(item);
                            if(tx != null) {
                                if(datasAll[item] !== undefined && datasAll[item]['TYPE'] !== undefined) {
                                    if(datasAll[item]['TYPE'] == type_table) {
                                        tx.setValue(JSON.parse(datas2Val[index]));
                                    } else {
                                        tx.setValue(datas2Val[index]);
                                        if(datasAll[item]['TYPE'] == type_checkbox && datasAll[item]['CHECKED'] !== undefined ) {
                                            if(datasAll[item]['CHECKED'] == 1) {
                                                tx.check();
                                            } else {
                                                tx.uncheck();
                                            }
                                        }
                                    }
                                } else {
                                    tx.setValue(datas2Val[index]);
                                }
                            }
                        });
                    }

                    if (components.tenCongDan) {
                        components.tenCongDan.setValue(data.tenCongDan);
                    }
                    if (components.diDong) {
                        components.diDong.setValue(data.diDong);
                    }
                    if (components.soCmnd && data.laDoanhNghiep != 1) {
                        components.soCmnd.setValue(data.soCmnd);
                    }
                    if (components.maTinhThanh) {
                        components.maTinhThanh.setValue(data.phuongXa.quanHuyen.maTinhThanh || MA_TINH_THANH);
                    }
                    if (components.maQuanHuyen) {
                        components.maQuanHuyen.setValue(data.phuongXa.maQuanHuyen || hoSoOnline.maQuanHuyenNop);
                    }
                    if (components.maPhuongXa) {
                        components.maPhuongXa.setValue(data.maPhuongXa || hoSoOnline.maPhuongXaNop);
                    }
                    if (components.diaChi) {
                        components.diaChi.setValue(data.diaChi);
                    }
                    if (components.ngayCapCmnd) {
                        components.ngayCapCmnd.setValue(data.ngayCapCmnd);
                    }
                    if (components.fax) {
                        components.fax.setValue(data.fax);
                    }
                    if (components.noiCapCmnd) {
                        components.noiCapCmnd.setValue(data.noiCapCmnd);
                    }
                    if (components.tenCoQuanToChuc) {
                        components.tenCoQuanToChuc.setValue(data.tenCoQuanToChuc);
                    }
                    if (components.email) {
                        components.email.setValue(data.email);
                    }
                    if (components.website) {
                        components.website.setValue(data.website);
                    }
                    if (components.danTocCongDan) {
                        components.danTocCongDan.setValue(data.danToc);
                    }
                    if (components.ngaySinhCongDan) {
                        components.ngaySinhCongDan.setValue(data.ngaySinh);
                    }
                    if (components.gioiTinhCongDan) {
                        components.gioiTinhCongDan.setValue(data.gioiTinh);
                    }
                    if (components.soGCNGP) {
                        components.soGCNGP.setValue(hoSoOnline.soGCNGP);
                    }
                    if (components.ngayCapGCNGP) {
                        components.ngayCapGCNGP.setValue(hoSoOnline.ngayCapGCNGP);
                    }
                    if (components.noiCapGCNGP) {
                        components.noiCapGCNGP.setValue(hoSoOnline.noiCapGCNGP);
                    }
                    if (components.maTinhCapCMND) {
                        components.maTinhCapCMND.setValue(hoSoOnline.maTinhCapCMND);
                    }
                    if (components.maDMDiaChi) {
                        components.maDMDiaChi.setValue(data.maDMDiaChi);
                    }
                    if (components.maDMQuocGia) {
                        components.maDMQuocGia.setValue(data.maDMQuocGia);
                    }
                    if (components.diaChiNuocNgoai) {
                        components.diaChiNuocNgoai.setValue(data.diaChiNuocNgoai);
                    }

                    serve(fieldset.init().initComponents());

                    var cur_disable = $('#form-content input, #form-content select, #form-content textarea');
                    cur_disable.prop('disabled', 'disabled');
                    cur_disable.attr('id', '');
                    cur_disable.attr('name', '');
                });
            </script>
        </section>


        <?php if($quyTrinhVilisXml) { ?>
            <section id="thong-tin-vilis" style="margin:30px 0 0 0">
                <h4 style="color:#06b12b"><i class="fa fa-globe"></i> Thông tin liên thông Vilis</h4>
                <!--        <div id="thong-tin-vilis-data">-->
                <!--            <i class="fa fa-refresh"></i> Loading...-->
                <!--        </div>-->
                <div id="thong-tin-vilis-data" class="panel panel-success">
                    <table class="table">
                        <colgroup>
                            <col width="30%">
                        </colgroup>
                        <tbody>
                        <tr>
                            <td><strong>Địa chỉ thửa đất </strong></td>
                            <td><?php echo $hoSoVilis->getPhuongXa()->getTenPhuongXa().', '.$hoSoVilis->getPhuongXa()->getQuanHuyen()->getTenQuanHuyen()  ?></td>
                        </tr>
                        <tr>
                            <td><strong>Loại giao dịch </strong></td>
                            <td><?php echo $hoSoVilis->getTenLoaiGiaoDich() ?></td>
                        </tr><tr>
                            <td><strong>Loại hồ sơ </strong></td>
                            <td><?php echo $hoSoVilis->getTenLoaiHoSoGiaoDich() ?></td>
                        </tr>
                        <td><strong>Thửa đất số </strong></td>
                        <td><?php echo $hoSoVilis->getSoThuTuThua() ?></td>
                        </tr>
                        <td><strong>Tờ bản đồ số </strong></td>
                        <td><?php echo $hoSoVilis->getSoHieuToBanDo() ?></td>
                        </tr>

                        <td><strong>Cá nhân/Tổ chức </strong></td>
                        <td><?php
                            switch ($hoSoVilis->getLaToChuc()) {
                                case '0': echo 'Là cá nhân'; break;
                                case '1': echo 'Là tổ chức'; break;
                            }
                            ?></td>
                        </tr>
                        <td><strong>Với tư cách </strong></td>
                        <td><?php
                            switch ($hoSoVilis->getTuCach()) {
                                case '1': echo 'Chủ sở hữu'; break;
                                case '2': echo 'Người được ủy quyền'; break;
                                case '3': echo 'Đại diện thừa kế khai trình'; break;
                            }
                            ?></td>
                        </tr>
                        <?php if (!empty($hoSoVilis->getGhiChuTuCach())): ?>
                            <td><strong>Thông tin ủy quyền </strong></td>
                            <td><?php echo $hoSoVilis->getGhiChuTuCach() ?></td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <script type="text/javascript">
                    //require(['NthLib'], function(Nth) {
                    //    var xml = '<?php //echo $quyTrinhVilisXml; ?>//';
                    //    var  fieldset = Nth.FormBuilder.Fieldset.__fromXml(xml);
                    //    $.each(fieldset.components,function(index,item){
                    //        item.disable();
                    //    });
                    //    $('#thong-tin-vilis-data').html(fieldset.getDomNode());
                    //
                    //});
                </script>
            </section>
        <?php } // endif: if($quyTrinhVilisXml) {?>
        <?php
        $vilisLuuGiayToDinhKem = (int)Entity\System\Parameter::fromId('vilis_LuuGiayToDinhKem')->getValue();
        if ($quyTrinhVilisXml && $vilisLuuGiayToDinhKem === 1) {
            ?>
            <section id='giay-to-vilis' style="margin:30px 0 0 0">
                <h4 style="color:#8A0A8A"><i class="fa fa-files-o"></i> Thành phần hồ sơ nộp Vilis</h4>
                <div class="panel panel-danger">
                    <table width="100%" class="table table-bordered table-striped tphs">
                        <thead>
                        <tr>
                            <th width="35%">Tên giấy tờ</th>
                            <th width="8%">Số bản chính</th>
                            <th width="8%">Số bản sao</th>
                            <th width="25%">Tệp tin</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        if(is_array($giayToVilis)){
                            foreach($giayToVilis['luuIgate'] as $giayTo){
                                $ext = pathinfo($giayTo['igatepath'], PATHINFO_EXTENSION);
                                echo '<tr>';
                                echo  sprintf('<td>%s</td>',$giayTo['tenVanBan']);
                                echo  sprintf('<td style="text-align:right;">%s</td>',$giayTo['soBanChinh']);
                                echo  sprintf('<td style="text-align:right;">%s</td>',$giayTo['soBanSao']);
                                if (empty($giayTo['igatepath'])) {
                                    echo '<td></td>';
                                } else {
                                    echo  sprintf('<td><a href="%s%s"><i class="file-icon file-%s"></i> %s</a></td>',SITE_ROOT,$giayTo['igatepath'],$ext,$giayTo['tenFile']);
                                }
                                echo '</tr>';
                            }
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php } // endif: if ($quyTrinhVilisXml && $vilisLuuGiayToDinhKem === 1) ?>

        <?php if (!($quyTrinhVilisXml && $vilisLuuGiayToDinhKem === 1)) { ?>
            <section id="thanh-phan-ho-so-wrapper" style="margin: 30px 0 0 0">
                <h4><i class="fa fa-folder-o"></i> Thành phần hồ sơ </h4>
                <?php
                if($dmGiayToCuaHoSo->count()):
                    $iterator = $dmGiayToCuaHoSo->getIterator();
                    while ($iterator->valid()) {
                        $item = $iterator->current();
                        ?>
                        <div id="wrapper-thong-tin-giay-to-<?php echo $item->getMaGiayTo() ?>" style="margin:0 0 20px 0">
                            <div class="panel panel-default" style="margin-bottom:0">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <?php
                                        if ($item->batBuocNop()) {
                                            $span = new Node('span', $item->layNoiDungBatBuoc(), [
                                                'class' => 'label label-info label-fill-out-sm'
                                            ]);
                                            echo $span->toString();
                                        }
                                        echo html_entity_decode($item->getGiayTo()->getTenGiayTo());
                                        if ((int) $item->getMaLoaiGiayTo() !== 0) {
                                            $span = new Node('span', '&nbsp;<em style=color:#ce4d10>(' . $item->getLoaiGiayTo()->getTenLoaiGiayTo() . ')</em>');
                                            echo $span->toString();
                                        }
                                        ?>
                                    </h4>
                                </div>
                                <table class="table" id="thong-tin-giay-to-<?php echo $item->getMaGiayTo() ?>">
                                    <colgroup>
                                        <col width="30%">
                                    </colgroup>
                                    <tbody>
                                    <tr>
                                        <td><strong>Số bản</strong></td>
                                        <td><?php echo $item->getSoBan() ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tệp tin</strong></td>
                                        <td><?php echo Files::fromString($item->getFileGiayTo())->display(['default_html' => true]) ?></td>
                                    </tr>
                                    </tbody>
                                </table>
                                <?php if($maBieuMau = $item->getMaBieuMau()): ?>
                                    <div id="wrapper-bieu-mau-giay-to-<?php echo $item->getMaGiayTo() ?>" class="panel-footer" style="display:none"></div>
                                    <script type="text/javascript">
                                        require(['NthLib'], function(Nth) {

                                            var convertor = new Nth.FormBuilder.Convertor();
                                            var data = JSON.parse('<?php echo addslashes(json_encode(Entity\BieuMau::fromMaBieuMau($maBieuMau, ['cache' => true])->toArray())); ?>');
                                            var item = JSON.parse('<?php echo addslashes(json_encode($item->toArray())); ?>');
                                            var fieldset = null;
                                            if (data.xmlBieuMau) {
                                                fieldset = Nth.FormBuilder.Fieldset.__fromXml(data.xmlBieuMau);
                                            } else if (data.htmlBieuMau) {
                                                fieldset = convertor.setHtml(data.htmlBieuMau).toFieldset();
                                            }
                                            if (fieldset) {
                                                fieldset.setEncodingData(item.formData);
                                                $('#wrapper-bieu-mau-giay-to-' + data.maGiayTo).html(fieldset.getDomNode()).show();
                                            }
                                        });
                                    </script>
                                <?php endif; ?>
                                <?php if(($template = $item->getTemplate()) && $template->exists()): ?>
                                    <div class="panel-body" id="action-giay-to-<?php echo $item->getMaGiayTo() ?>" style="border-top: 1px solid #ddd">
                                        <a href="<?php echo sprintf('%bo-cong-an/tiep-nhan-online/export-template?sid=%s&template-id=%s', SITE_ROOT, $queryData['sid'], $template->getId()) ?>" class="btn btn-default btn-sm"><i class="fa fa-file-text-o"></i> Xuất mẫu đơn</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php
                        $iterator->next();
                    }
                else:
                    ?>
                    <p style="color:#555"><em>(Hồ sơ không có giấy tờ kèm theo)</em></p>
                <?php endif; ?>
            </section>
        <?php } // endif: if (!($quyTrinhVilisXml && $vilisLuuGiayToDinhKem === 1)) ?>
        <?php if($maBieuMau = $hoSoOnline->getMaBieuMau()): ?>
            <section id="thong-tin-cung-cap-them-wrapper" style="margin: 30px 0 0 0;display:none">
                <h4 style="color:#3ab31b"><i class="fa fa-file-text-o"></i> Thông tin cung cấp thêm</h4>
                <div id="thong-tin-cung-cap-them-content"></div>
            </section>
            <script type="text/javascript">
                require(['NthLib'], function(Nth) {
                    var convertor = new Nth.FormBuilder.Convertor();
                    var data = JSON.parse('<?php echo addslashes(json_encode(Entity\BieuMau::fromMaBieuMau($maBieuMau, ['cache' => true])->toArray())); ?>');
                    var hoSoOnline = JSON.parse('<?php echo addslashes(json_encode($hoSoOnline->toArray())); ?>');
                    var fieldset = null;
                    if (data.xmlBieuMau) {
                        fieldset = Nth.FormBuilder.Fieldset.__fromXml(data.xmlBieuMau);
                    } else if (data.htmlBieuMau) {
                        fieldset = convertor.setHtml(data.htmlBieuMau).toFieldset();
                    }
                    if (fieldset) {
                        fieldset.setEncodingData(hoSoOnline.duLieuBieuMau);
                        $('#thong-tin-cung-cap-them-content').html(fieldset.getDomNode());
                        $('#thong-tin-cung-cap-them-wrapper').show();

                        var cur_disable2 = $('#thong-tin-cung-cap-them-content input, #thong-tin-cung-cap-them-content select, #thong-tin-cung-cap-them-content textarea');
                        cur_disable2.prop('disabled', 'disabled');
                        cur_disable2.attr('id', '');
                        cur_disable2.attr('name', '');
                    }
                });
            </script>
        <?php endif; ?>
        <?php $lienThongVilis = (int) Entity\System\Parameter::fromId('LIEN_THONG_VILIS')->getValue(); ?>
        <?php if(!$lienThongVilis): ?>
            <?php if($hoSoOnline->coThongTinKhac()|| count($hoSoOnline->getDmGiayToKhac()->getItems() > 0)): ?>
                <section id="thong-tin-khac-wrapper" style="margin: 30px 0 0 0">
                    <h4 style="color:#bd8914"><i class="fa fa-hand-o-right"></i> Thông tin khác</h4>
                    <?php if (count($hoSoOnline->getDmGiayToKhac()->getItems() > 0)) : ?>
                        <fieldset id="giaytokhac_hs_wp" class="form-wrapper"></fieldset>
                        <script type="text/javascript">
                            require(['NthLib'], function(Nth) {
                                var dmGiayToKhac = JSON.parse('<?php echo addslashes(json_encode($hoSoOnline->getDmGiayToKhac()->toArray())); ?>');
                                var giayTos = [];
                                if(dmGiayToKhac instanceof Array){
                                    dmGiayToKhac.forEach(function(item){
                                        giayTos.push({
                                            'id': item.maCoGiayToKhac,
                                            'filename' : item.tenGiayTo,
                                            'filepath' : item.fileGiayTo ? item.fileGiayTo : null
                                        });
                                    });
                                }
                                var giayToKhac = new Nth.FormBuilder.Element.ListFile('giayToKhac', {
                                    label: 'Giấy tờ khác',
                                    value: giayTos,
                                    disabled: true
                                });
                                $('#giaytokhac_hs_wp').html(giayToKhac.getDomNode());
                            });
                        </script>
                    <?php endif; ?>
                    <div class="panel panel-warning">
                        <table class="table">
                            <colgroup>
                                <col width="30%">
                            </colgroup>
                            <tbody>
                            <?php if($fileGiayToKhac = $hoSoOnline->getFileGiayToKhac()): ?>
                                <tr>
                                    <td><strong>Tệp tin thành phần hồ sơ khác</strong></td>
                                    <td><?php echo Files::fromString($fileGiayToKhac)->display(['default_html' => true]) ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if($ghiChu = $hoSoOnline->getGhiChu()): ?>
                                <tr>
                                    <td><strong>Ghi chú</strong></td>
                                    <td><?php echo $ghiChu; ?></td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            <?php endif; ?>
        <?php endif; ?>

        <section id="thong-tin-le-phi-wrapper" style="margin:30px 0 0 0">
            <h4 style="color:#0fbd12"><i class="fa fa-credit-card"></i> Thông tin chung</h4>
            <div class="panel panel-success">
                <table class="table">
                    <colgroup>
                        <col width="30%">
                    </colgroup>
                    <tbody>
                    <?php if ($hoSoOnline->getDanhSachGiayToNop()->coGiayToPhaiNopGiay()) : ?>
                        <tr>
                            <td><strong>Hình thức nộp hồ sơ</strong></td>
                            <td>
                                <?php
                                echo $hoSoOnline->getHinhThucNop()->getTenHinhThuc();
                                if ((int) $hoSoOnline->getMaHinhThucNop() === 1) {
                                    echo sprintf(' <em>(%s)</em>', implode(', ', [
                                        $hoSoOnline->getNgayYeuCauThuGom('d/m/Y H:i')
                                        , $hoSoOnline->getDiaChiThuGom()
                                        , $hoSoOnline->getPhuongXaThuGom()->getTenPhuongXa()
                                        , $hoSoOnline->getPhuongXaThuGom()->getQuanHuyen()->getTenQuanHuyen()
                                        , $hoSoOnline->getPhuongXaThuGom()->getQuanHuyen()->getTinhThanh()->getTenTinhThanh()
                                    ]));
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php if((int) $hoSoOnline->getMaHinhThucNop() === 1 && $qttt->getThoiGianThuGom()): ?>
                        <tr>
                            <td><strong>Thời gian thu gom</strong></td>
                            <td><?php echo $qttt->layThoiGianThuGomHienThi(); ?></td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <td><strong>Hình thức nhận kết quả</strong></td>
                        <td>
                            <?php
                            echo $hoSoOnline->getHinhThucNhanKetQua()->getTenHinhThuc();
                            if ((int) $hoSoOnline->getMaHinhThucNhanKetQua() === 1) {
                                echo sprintf(' <em>(%s)</em>', implode(', ', [
                                    $hoSoOnline->getDiaChiNhanKetQua()
                                    , $hoSoOnline->getPhuongXaNhanKetQua()->getTenPhuongXa()
                                    , $hoSoOnline->getPhuongXaNhanKetQua()->getQuanHuyen()->getTenQuanHuyen()
                                    , $hoSoOnline->getPhuongXaNhanKetQua()->getQuanHuyen()->getTinhThanh()->getTenTinhThanh()
                                ]));
                            }
                            ?>
                        </td>
                    </tr>
                    <?php if((int) $hoSoOnline->getMaHinhThucNhanKetQua() === 1 && $qttt->getThoiGianPhatTra()): ?>
                        <tr>
                            <td><strong>Thời gian phát trả kết quả</strong></td>
                            <td><?php echo $qttt->layThoiGianPhatTraHienThi(); ?></td>
                        </tr>
                    <?php endif; ?>
                    <?php if($maHinhThucThanhToan = (int) $hoSoOnline->getMaHinhThucThanhToan()): ?>
                        <tr>
                            <td><strong>Phương thức thanh toán</strong></td>
                            <td><?php echo $hoSoOnline->getHinhThucThanhToan()->getTenHinhThuc() ?></td>
                        </tr>
                        <?php if($maHinhThucThanhToan === 1): //thanh toan qua buu dien ?>
                            <tr>
                                <td><strong>Bưu cục thanh toán</strong></td>
                                <td>
                                    <?php
                                    echo sprintf('%s <em>(%s)</em>', $hoSoOnline->getBuuCucThanhToan()->getTenBuuCuc(), implode(', ', array_filter([
                                        $hoSoOnline->getBuuCucThanhToan()->getDiaChi()
                                        , $hoSoOnline->getBuuCucThanhToan()->getPhuongXa()->getTenPhuongXa()
                                        , $hoSoOnline->getBuuCucThanhToan()->getPhuongXa()->getQuanHuyen()->getTenQuanHuyen()
                                        , $hoSoOnline->getBuuCucThanhToan()->getPhuongXa()->getQuanHuyen()->getTinhThanh()->getTenTinhThanh()
                                    ])));
                                    ?>
                                </td>
                            </tr>
                            <?php if($soHoaDon = $hoSoOnline->getSoHoaDonThanhToan()): ?>
                                <tr>
                                    <td><strong>Số hóa đơn thanh toán</strong></td>
                                    <td><?php echo $soHoaDon; ?></td>
                                </tr>
                            <?php endif; ?>
                        <?php elseif($maHinhThucThanhToan === 2): //thanh toan qua ngan hang ?>
                            <tr>
                                <td><strong>Chi nhánh ngân hàng thanh toán</strong></td>
                                <td>
                                    <?php
                                    echo sprintf('%s <em>(%s)</em>', $hoSoOnline->getCNNganHangThanhToan()->getTenChiNhanh(), implode(', ', array_filter([
                                        $hoSoOnline->getCNNganHangThanhToan()->getDiaChi()
                                        , $hoSoOnline->getCNNganHangThanhToan()->getPhuongXa()->getTenPhuongXa()
                                        , $hoSoOnline->getCNNganHangThanhToan()->getPhuongXa()->getQuanHuyen()->getTenQuanHuyen()
                                        , $hoSoOnline->getCNNganHangThanhToan()->getPhuongXa()->getQuanHuyen()->getTinhThanh()->getTenTinhThanh()
                                    ])));
                                    ?>
                                </td>
                            </tr>
                            <?php if($soHoaDon = $hoSoOnline->getSoHoaDonThanhToan()): ?>
                                <tr>
                                    <td><strong>Số hóa đơn</strong></td>
                                    <td><?php echo $soHoaDon; ?></td>
                                </tr>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
        <section id="le-phi-nop-1-wrapper" style="margin:30px 0 0 0">
            <h4 style="color:#d8ac0b"><i class="fa fa-money"></i> Lệ phí thanh toán cho cơ quan giải quyết <small>Đơn vị tính: <strong>VNĐ</strong></small></h4>
            <div class="form-wrapper">
                <div class="panel panel-info">
                    <table class="table">
                        <colgroup>
                            <col width="35%"/>
                            <col width="10%"/>
                            <col width="25%"/>
                            <col width="5%"/>
                            <col width="15%"/>
                        </colgroup>
                        <thead>
                        <tr>
                            <td><strong>Loại lệ phí</strong></td>
                            <td data-toggle="tooltip" title="Số lượng giấy tờ mà người nộp hồ sơ muốn cơ quan giải quyết "><strong>Số lượng</strong></td>
                            <td class="amount"><strong>Mức lệ phí</strong></td>
                            <td></td>
                            <td></td>
                        </tr>
                        </thead>
                        <tbody id="tbody-1">
                        <?php
                        $total = 0;
                        $payout = null;
                        $countLP = count($dmLePhiHoSo->filter(['fromDatabase' => false, 'thanhToanCho' => 1]));
                        foreach ($dmLePhiHoSo->filter(['fromDatabase' => false, 'thanhToanCho' => 1]) as $item) {
                            $total += (int) ($item->getMucLePhi() * $item->getSoLuong());
                            $paid = $item->daThanhToan();
                            if ($paid === false) {
                                $payout = false;
                            } elseif ($payout === null) {
                                $payout = true;
                            }
                            ?>
                            <tr class="<?php echo $paid ? 'paid' : '' ?>">
                                <td class="name"><?php echo $item->getLoaiLePhi()->getTenLoai(); ?></td>
                                <td class="name"><?php echo $item->getSoLuong(); ?></td>
                                <td class="amount"><?php echo Number::addCommas($item->getMucLePhi()) ?></td>
                                <td class="unit"><strong>VNĐ</strong></td>
                                <td class="desc">
                                    <?php if($paid): ?>
                                        <span><i class="fa fa-check"></i> Đã thanh toán</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                        </tbody>
                        <tfoot>
                        <tr id="tong-cong-1" class="<?php echo $payout ? 'paid' : '' ?>">
                            <td class="name"><strong>Tổng cộng</strong></td>
                            <td class="name"></td>
                            <td class="amount total"><?php echo Number::addCommas($total) ?></td>
                            <td class="unit"><strong>VNĐ</strong></td>
                            <td class="desc">
                                <?php if($payout): ?>
                                    <span><i class="fa fa-check"></i> Đã thanh toán</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </section>
        <script type="text/javascript">
            <?php if($total == 0): ?>
            require(['jquery'], function(Nth, queryData) {
                $.ajax(SITE_ROOT + 'dichvucong/tiepnhanonline/checkAnHienLePhi', {
                    type: "POST",
                    data: {
                        count: '<?php echo $countLP; ?>'
                    },
                    success: function (r) {
                        console.log(r);
                        if(r == 1){
                            $('#le-phi-nop-1-wrapper').hide();
                            $('#le-phi-nop-2-wrapper').hide();
                        }
                    }
                });
            });
            <?php endif; ?>
        </script>

        <?php if(count($items = $dmLePhiHoSo->filter(['fromDatabase' => false,'thanhToanCho' => 2]))): ?>
            <section id="le-phi-nop-2-wrapper" style="margin:30px 0 0 0;">
                <h4 style="color:#0fbd85"><i class="fa fa-truck"></i> Lệ phí thanh toán cho bưu điện <small>Đơn vị tính: <strong>VNĐ</strong></small></h4>
                <div class="panel panel-default">
                    <table class="table">
                        <colgroup>
                            <col width="35%"/>
                            <col width="25%"/>
                            <col width="5%"/>
                        </colgroup>
                        <tbody id="tbody-2">
                        <?php
                        $total = 0;
                        $payout = null;
                        foreach ($items as $item) {
                            $total += (int) $item->getMucLePhi();
                            $paid = $item->daThanhToan();
                            if ($paid === false) {
                                $payout = false;
                            } elseif ($payout === null) {
                                $payout = true;
                            }
                            ?>
                            <tr class="<?php echo $paid ? 'paid' : '' ?>">
                                <td class="name"><?php echo $item->getLoaiLePhi()->getTenLoai(); ?></td>
                                <td class="amount"><?php echo Number::addCommas($item->getMucLePhi()) ?></td>
                                <td class="unit"><strong>VNĐ</strong></td>
                                <td class="desc">
                                    <?php if($paid): ?>
                                        <span><i class="fa fa-check"></i> Đã thanh toán</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                        </tbody>
                        <tfoot>
                        <tr id="tong-cong-2" class="<?php echo $payout ? 'paid' : '' ?>">
                            <td class="name"><strong>Tổng cộng</strong></td>
                            <td class="amount total"><?php echo Number::addCommas($total) ?></td>
                            <td class="unit"><strong>VNĐ</strong></td>
                            <td class="desc">
                                <?php if($payout): ?>
                                    <span><i class="fa fa-check"></i> Đã thanh toán</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
                <?php if((int) Entity\System\Parameter::fromId('DOI_TUONG_GIAM_CUOC_VNPOST')->getValue() === 1 && !empty($dtMienGiamCuocVNPost)) : //IGATESUPP-26470 tttruong-kv1
                    $dt = $model->db->ExecuteCursor("BEGIN LCI_HOME.DM_DT_GIAM_PHI_VNPOST(:P_CUR);END;", 'P_CUR');
                    ?>
                    <div class="row">
                        <div class="col-md-5">
                            <p class="" style="font-weight: 600;">Đối tượng được giảm phí: </p>
                        </div>
                        <div class="col-md-5">
                            <select name="loaidt_vnpost" id="chonDoiTuong" class="form-control" disabled="">
                                <?php foreach ($dt as $item ) : ?>
                                    <option value="<?php echo $item['MA_DM_DT_GIAM_PHI'] ?>" <?php echo ($item['MA_DM_DT_GIAM_PHI'] == $dtMienGiamCuocVNPost['madt']) ? 'selected' : '' ?>
                                            title="<?php echo $item['TEN_DM_DT_GIAM_PHI'] ?>"> <?php echo $item['TEN_DM_DT_GIAM_PHI'] ?></option>
                                <?php endforeach;?>
                            </select>
                        </div>
                    </div>
                    <div class="row" style="margin-top: 10px">
                        <div class="col-md-5">
                            <p class="" style="font-weight: 600;line-height: 34px;margin: 0;">Tổng tiền phải thanh toán cho bưu điện là:</p>
                        </div>
                        <div class="col-md-4">
                            <input type="hidden" value="" name="txt_tienDoiTuongGiam" id="txt_tienDoiTuongGiam">
                            <p id="tongTien" style="font-weight: 600;line-height: 34px;margin: 0;"><?php echo number_format($dtMienGiamCuocVNPost['tienphaitra'], 0, '.', ',') ?> VNĐ</p>
                        </div>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>

        <?php

        $labelInGCN = Model\Entity\System\Parameter::fromId('GIAY_TO_XUAT_RA_FRONTEND_LABEL')->getValue();
        if ($labelInGCN == '' or empty($labelInGCN)) {
            $labelInGCN = 'In GCN';
        }
        $package = new Package\TEMPLATE();
        $rs = $package->SELECT_GIAY_TO_XUAT_RA([
            'P_MA_THU_TUC' => $thuTuc->getMaThuTuc(),
            'P_TRANG_THAI' => 1,
            'P_NOI_HIEN_THI' => 1
        ]);
        $count = $rs->count();
        if ($count === 1) {
            $a = new Node('a', $labelInGCN, [
                'class' => 'btn btn-success',
                'href' => SITE_ROOT . 'ho-so/xuat-giay-to/gcnTiepNhanOnline?' . http_build_query([
                        'ma-giay-to-xuat-ra' => $rs->offsetGet(0)->MA_GIAY_TO_XUAT_RA,
                        'sid' => $queryData['sid']
                    ]),
                'target' => "_blank"
            ]);
            echo $a->toString();
        } elseif ($count > 1) {
            $div = new Node('div', null, ['class' => 'btn-group']);
            $button = new Node('button', $labelInGCN. ' <span class="caret"></span>', [
                'class' => "btn btn-default dropdown-toggle",
                'data-toggle' => "dropdown",
                'aria-haspopup' => "true",
                'aria-expanded' => "false"
            ]);
            $ul = new Node('ul', null, ['class' => "dropdown-menu"]);
            $div->appendChild($button, $ul);

            $iterator = $rs->getIterator();
            while ($iterator->valid()) {
                $current = $iterator->current();
                $a = new Node('a', $current->TEN_GIAY_TO_XUAT_RA, [
                    'href' => SITE_ROOT . 'ho-so/xuat-giay-to/gcnTiepNhanOnline?' . http_build_query([
                            'ma-giay-to-xuat-ra' => $current->MA_GIAY_TO_XUAT_RA,
                            'sid' => $queryData['sid']
                        ]),
                    'target' => "_blank"
                ]);
                $ul->appendChild(new Node('li', $a));
                $iterator->next();
            }
            echo $div->toString();
        }

        ?>

        <section id="ma-xac-nhan-wrapper" style="margin:30px 0 0 0">
            <div id="ma-xac-nhan-form">
                <i class="fa fa-refresh"></i> Loading...
            </div>
            <script type="text/javascript">
                require(['NthLib', 'queryData'], function(Nth, queryData) {
                    __current.hiddenData = queryData;
                    var fieldset = new Nth.FormBuilder.Fieldset('fieldset-security');
                    var row_1 = new Nth.FormBuilder.Row('security-row', {
                        parentComponent: fieldset
                    });
                    var row_2 = new Nth.FormBuilder.Row('guarantee-row', {
                        parentComponent: fieldset
                    });
                    var column_1 = new Nth.FormBuilder.Column('security-column-1', {
                        parentComponent: row_1
                    });
                    var column_2 = new Nth.FormBuilder.Column('security-column-2', {
                        parentComponent: row_1
                    });
                    var column_3 = new Nth.FormBuilder.Column('security-column-3', {
                        parentComponent: row_2,
                        wrapperAttributes: {
                            'class': 'col-md-12'
                        }
                    });
                    var maXacNhan = new Nth.FormBuilder.Element.Captcha('maXacNhan', {
                        parentComponent: column_1,
                        initUrl: function() {
                            return this.getConfigParam('siteRoot') + 'captcha.php?act=create&t=' + new Date().getTime() + '&for=' + this.getName()
                        },
                        validUrl: function() {
                            return this.getConfigParam('siteRoot') + 'captcha.php?act=check&code=' + this.getValue() + '&of=' + this.getName();
                        },
                        imageContainer: column_2.getWrapper().getNode(),
                        label: 'Mã xác nhận',
                        required: 1
                    });
                    var guarantee = new Nth.FormBuilder.Element.Checkbox('guarantee', {
                        parentComponent: column_3,
                        label: 'Tôi xin chịu trách nhiệm trước pháp luật về lời khai trên',
                        required: true,
                        marksMandatory: false
                    });
                    fieldset.addComponent(row_1, row_2);
                    fieldset.addComponent(column_1, column_2, column_3);
                    fieldset.addComponent(maXacNhan, guarantee);
                    $('#ma-xac-nhan-form').html(fieldset.getDomNode());
                    function validate(fn) {
                        fieldset.isValid(function () {
                            var message = this.getMessage();
                            if (message) {
                                var inst = this;
                                return Nth.Alert(message, function () {
                                    inst.focus();
                                });
                            }
                            __current.createHiddenInputs();
                            fn.call();
                        });
                    }
                    $('#btn-save').removeAttr('disabled').on('click', function () {
                        validate(function () {
                            $('#mainForm').attr('action', SITE_ROOT + 'bo-cong-an/tiep-nhan-online/luuHoSo').submit();
                        });
                    });
                    $('#btn-apply').removeAttr('disabled').on('click', function () {
                        validate(function () {
                            $('#mainForm').attr('action', SITE_ROOT + 'bo-cong-an/tiep-nhan-online/nopHoSo').submit();
                        });
                    });
                    $('#btn-huy-ho-so').removeAttr('disabled').on('click', function () {
                        Nth.Confirm('Có chắc bạn muốn hủy hồ sơ này?', function (choosed) {
                            if (Nth.Confirm.OK === choosed) {
                                window.location.href = SITE_ROOT + 'bo-cong-an/tiep-nhan-online/huy-ho-so?sid=<?php echo $queryData['sid'] ?>' + '&token=' + '<?php echo $token ?>';
                            }
                        });
                    });
                });
            </script>
        </section>
        <section id="page-action-wrapper" style="margin: 50px 0 50px 0">
            <div class="row">
                <div class="col-xs-6">
                    <a class="btn btn-default" id="btnBack" href="<?php echo $progress->getPrevStep()->getLink() ?>"><i class="fa fa-arrow-left"></i> Quay lại</a>
                </div>
                <div class="col-xs-6 text-right">
                    <?php if ($hoSoOnline->getTrangThaiHoSo() == 'DA_TIEP_NHAN'): ?>
                        <span style="color: red"><p><i>Hồ sơ đã được cán bộ tiếp nhận không thể thay đổi thông tin hồ sơ</i></p></span>
                    <?php else: ?>
                        <?php if($hoSoOnline->duocPhepHuy()): ?>
                            <button id="btn-huy-ho-so" class="btn btn-danger" disabled="true"><i class="fa fa-trash"></i> Hủy hồ sơ</button>
                        <?php endif; ?>
                        <?php if(Entity\CongDan::hasSession()): ?>
                            <button type="button" class="btn btn-success" disabled="true" id="btn-save"><i class="fa fa-save"></i> Lưu hồ sơ</button>
                        <?php endif; ?>
                        <?php if($hoSoOnline->phaiThanhToanTrucTuyen() && $tongLePhiChuaThanhToan > 0): ?>
                            <button type="button" class="btn btn-warning" disabled="true" id="btn-apply"><i class="fa fa-hand-o-right"></i> Thanh toán & Nộp hồ sơ</button>
                        <?php else: ?>
                            <button type="button" class="btn btn-primary" disabled="true" id="btn-apply"><i class="fa fa-hand-o-right"></i> Nộp hồ sơ</button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        <script>
            require(['NthLib'], function(Nth) {
            });

            var link_was_clicked = false;
            document.addEventListener("click", function(e) {//console.log(e);
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
$this->template->display('bocongan/frontend.optimize-footer.php');
