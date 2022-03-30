<?php

if (!defined('SERVER_ROOT')) {
    exit('No direct script access allowed');
}

use Model\Entity;
use Model\DanhMuc;
use Model\DichVuCong\Applier;
use Model\DichVuCong\Progress;
use Zend\Validator\Csrf;
use Nth\ResultInfo;
use Model\System;
use Model\Entity\LogData;
use Oracle\Package;

use Zend\Http\Request;
use Zend\Http\Client;
use Zend\Stdlib\Parameters;
use Nth\File\Folder;
use Zend\Cache\StorageFactory;
use Zend\Debug\Debug;

class tiepnhanonline_Controller extends Controller {

    public function __construct() {
        parent::__construct('bocongan', 'tiepnhanonline');
        $this->model->call('hososingle');
        $this->model->call('giaytohoso');
        $this->appendStylesheet([
            'public/portal/js/jquery-ui-bootstrap/css/custom-theme/jquery-ui-1.10.0.custom' => ['cache' => true],
            'public/libs/bootstrap-3.3.7-dist/css/bootstrap.min' => ['cache' => true],
            'public/libs/font-awesome-4.7.0/css/font-awesome.min' => ['cache' => true],
            'public/fonts/Open_Sans/style' => ['cache' => true],
            'public/template/igate-bootstrap/igate-bootstrap',
            'public/css/iCheck' => ['cache' => true],
            'public/css/iCheck/all' => ['cache' => true],
            'public/js/Nth/FormBuilder/Css/Frontend',
            'public/js/Nth/File/Css/FileIcon',
            'public/css/steps-progress/style' => ['cache' => false],
            'public/libs/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min' => ['cache' => false],
        ]);
        $this->appendJavascript([
            'public/js/function_helper' => ['cache' => true]
        ]);
    }

    public function ajaxSelectCoQuanByThuTucPublic() {
        $model = $this->getModel();
        $options = $model->getPostData();
        echo $model->layDanhMucCoQuanByThuTucPublic($options)->toHtmlOptions(function ($item) {
            return $item->getMaCoQuan();
        }, function($item) {
            return $item->getTenCoQuan();
        }, null, function ($count) {
            return $count !== 1;
        });
    }

    public function ajaxSelectQtttByThuTucPublic() {
        $model = $this->getModel();
        $options = $model->getPostData();
        echo $model->layDanhMucQtttNopHoSo($options)->toHtmlOptions(function ($item) {
            return $item->getMaQttt();
        }, function($item) {
            return $item->layChuoiHienThi();
        }, null, function ($count) {
            return $count !== 1;
        });
    }

    public function ajaxSelectQuanHuyenNopHoSo() {
        $model = $this->getModel();
        $options = $model->getPostData();
        echo $model->layDanhMucQuanHuyenNopHoSo($options)->toHtmlOptions(function ($item) {
            return $item->getMaQuanHuyen();
        }, function($item) {
            return $item->getTenQuanHuyen();
        }, null, function ($count) {
            return $count !== 1;
        });
    }

    public function ajaxSelectPhuongXaNopHoSo() {
        $model = $this->getModel();
        $options = $model->getPostData();
        echo $model->layDanhMucPhuongXaNopHoSo($options)->toHtmlOptions(function ($item) {
            return $item->getMaPhuongXa();
        }, function($item) {
            return $item->getTenPhuongXa();
        }, null, function ($count) {
            return $count !== 1;
        });
    }

    public function ajaxTinhCuocThuGomHoSo() {
        echo json_encode($this->getModel()->tinhCuocThuGomHoSo());
    }

    public function ajaxTinhCuocPhatTraHoSo() {
        echo json_encode($this->getModel()->tinhCuocPhatTraHoSo());
    }

    public function main() {
        $this->chonThuTucNopHoSo();
    }

    public function chonThuTucNopHoSo() {
        if ((int) Entity\System\Parameter::fromId('DVC_BAT_BUOC_DANG_NHAP')->getValue() === 1) {
            Entity\CongDan::forceLoginIfDoesNotDVC($this->getRequest()->getRequestUri());
        }
        $this->getView()->render('thu-tuc-nop-ho-so', [
            'model' => $this->getModel(),
            'progress' => new Progress(new Applier(), 1, 4)
        ]);
    }

    public function chonTruongHopHoSo() {
        if ((int) Entity\System\Parameter::fromId('vnConnectDVCQG')->getValue() === 1) {
            $request = new Model\Config\vnConnect\Request();
            if($request->checkRequestUrl($this->getRequest(), $this->getFilter())){
                $vnconnect = new Model\Config\vnConnect\Oauth();
                $vnconnect->setSessionRequest();
                header('Location:'.$vnconnect->getRedirectLogin());
                exit;
            }
        }
        if ((int) Entity\System\Parameter::fromId('DVC_BAT_BUOC_DANG_NHAP')->getValue() === 1) {
            Entity\CongDan::forceLoginIfDoesNotDVC($this->getRequest()->getRequestUri());
        }
        //BCA bat buoc dang nhap, dung site dang nhap của dvc - Hienctt KV1
        if ((int) Entity\System\Parameter::fromId('BCA_SERVICE_ACTIVE')->getValue() === 1) {
            $request = $this->getRequest();
            $filter = $this->getFilter();
            $maThuTucPublic = $filter->filter($request->getQuery('ma-thu-tuc-public'));

            // yêu cầu login igate theo thủ tục
            $thutuc_yc_login_igate = Model\Entity\System\Parameter::fromId('BCA_THUTUC_YC_LOGIN_IGATE')->getValue();
            $require_tt_ig = explode(',', $thutuc_yc_login_igate);
            if(in_array($maThuTucPublic, $require_tt_ig)) {
                Entity\CongDan::forceLoginIfDoesNotDVC($this->getRequest()->getRequestUri());
            }

            // yêu cầu tài khoản login phải đã sso lên cổng quốc gia
            $this->checkSSOTKQUOCGIA($maThuTucPublic);

            // Check nếu không phải số hồ sơ được cấu hình theo url BCA
            $this->checkQuyTrinhBCA($maThuTucPublic);

            // check có phải thủ tục Khai báo tạm trú và tài khoản công dân BCA đã đăng nhập hay chưa
            $this->checkThuTucKBTT($maThuTucPublic, $this->getRequest()->getRequestUri(), false);
        }
        $data['model'] = $this->getModel();
        $data['model']->kiemTraTruongHopHoSo();
        $this->getView()->render('truong-hop-ho-so', $data);
    }

    public function nhapThongTinNguoiNopHoSo() {

        $data['model'] = $this->getModel();
        $data['queryData'] = $data['model']->getQueryData();
        $data['applier'] = new Applier($data['queryData']['sid']);
        $data['hoSoOnline'] = $data['applier']->sessionGet(Applier::ET_HO_SO_ONLINE);
        if (!empty($data['hoSoOnline']) && !empty($data['hoSoOnline']->getMaHoSo()))  { // CCCD: check có được cập nhật hay không
            $this->checkCanUpdateORDeleteCCCD($data['hoSoOnline']->getSoHoSo(), $data['queryData']['sid']);
        }

        $data['model']->checkUpdateRequirement($data['queryData']['sid']);
        $data['qttt'] = $data['hoSoOnline']->getQttt();
        $data['thuTuc'] = $data['hoSoOnline']->getThuTuc();
        $data['mucDo'] = $data['thuTuc']->getMucDo();
        $data['donViTiepNhan'] = $data['hoSoOnline']->getDonViTiepNhan();
        $data['congDan'] = $data['hoSoOnline']->getCongDan();
        $data['chuHoSo'] = $data['hoSoOnline']->getChuHoSo() != null ? $data['hoSoOnline']->getChuHoSo() : (Entity\ChuHoSo::fromMaHoSoOnline($data['hoSoOnline']->getMaHoSo()));

        $lbm = $data['hoSoOnline']->layMaEformID();
        if($lbm)
        {
            if(!empty($data['hoSoOnline']) && !empty($data['hoSoOnline']->getMaHoSo()) && $data['queryData']['sid'] == $data['hoSoOnline']->getMaHoSo()) {
                $bmTDL = $this->getModel()->layDanhSachDuLieuTDL(null, $lbm, null, $data['queryData']['sid'])->getDefaultResult();
            } else {
                $bmTDL = $this->getModel()->layDanhSachDuLieuTDL($data['queryData']['sid'], $lbm, null, null)->getDefaultResult();
            }
            $cn = [];
            $val = [];
            $val_total = [];
            foreach ($bmTDL as $key => $value) {
                array_push($cn,$value['CONTROL_NAME']);
                array_push($val,$value['GIA_TRI']);
                $val_total[$value['CONTROL_NAME']] = [
                    'GIA_TRI' => $value["GIA_TRI"],
                    'CHECKED' => (int)$value["CHECKED"],
                    'TYPE' => $value["TYPE"]
                ];
            }
            $data['controlName'] = $cn;
            $data['giaTri'] = $val;
            $data['giaTriBML'] = $val_total;
        }
        else
        {
            $data['controlName'] = null;
            $data['giaTri'] = null;
            $data['giaTriBML'] = null;
        }

        $MA_THU_TUC = $data['thuTuc']->getMaThuTuc();
        $tsqt_nop_hs_2b = Model\Entity\System\Parameter::fromId('BCA_CAU_HINH_TT_QUY_TRINH_NOP_HS_HAI_BUOC')->getValue();
        $tsqt_nop_hs_3b = Model\Entity\System\Parameter::fromId('BCA_CAU_HINH_TT_QUY_TRINH_NOP_HS_BA_BUOC')->getValue();
        $qt_nop_hs_2b = $tsqt_nop_hs_2b ? explode(',', $tsqt_nop_hs_2b) : [];
        $qt_nop_hs_3b = $tsqt_nop_hs_3b ? explode(',', $tsqt_nop_hs_3b) : [];
        if(in_array($MA_THU_TUC, $qt_nop_hs_2b) || in_array($MA_THU_TUC, $qt_nop_hs_3b)){
            $data['progress'] = new Progress($data['applier'], 2, 4);
        }else{
            $data['progress'] = new Progress($data['applier'], 2, 7);
        }

        // yêu cầu tài khoản login phải đã sso lên cổng quốc gia
        $this->checkSSOTKQUOCGIA($MA_THU_TUC);

        // Check nếu không phải số hồ sơ được cấu hình theo url BCA
        $this->checkQuyTrinhBCA($MA_THU_TUC);

        // check có phải thủ tục Khai báo tạm trú và tài khoản công dân BCA đã đăng nhập hay chưa
        //  => redirect trang đăng nhập riêng
        $this->checkThuTucKBTT($MA_THU_TUC, $this->getRequest()->getRequestUri(), true);

        // thêm jquery
        $data['MA_THU_TUC'] = $MA_THU_TUC;
        $jquery_thutuc = getThamSoArray(Entity\System\Parameter::fromId("BCA_TT_JQUERY_FORM_THONG_TIN_NGUOI_NOP")->getValue());
        $data['jqueryTT'] = '';
        if(!empty($jquery_thutuc[$MA_THU_TUC])) {
            $data['jqueryTT'] = $jquery_thutuc[$MA_THU_TUC];
        }

        if(in_array($MA_THU_TUC, $qt_nop_hs_2b)) {
            $this->getView()->render('thong-tin-nguoi-nop-ho-so', $data);
        } else if(in_array($MA_THU_TUC, $qt_nop_hs_3b)) {
            $this->getView()->render('thong-tin-nguoi-nop-ho-so-3b', $data);
        } else {
            $this->getView()->render('thong-tin-nguoi-nop-ho-so', $data);
        }
    }

    public function luuThongTinNguoiNopHoSo() {
        $this->getModel()->luuThongTinNguoiNopHoSo();
    }

    public function luuThongTinNguoiNopHoSo3b() {
        $this->getModel()->luuThongTinNguoiNopHoSo3b();
    }

    public function luuLePhiHoSo() {
        $this->getModel()->luuLePhiHoSo();
    }
    //buoc5
    public function xacNhanThongTinNop() {
        $data['model'] = $this->getModel();
        $data['queryData'] = $data['model']->getQueryData();
        $data['applier'] = new Applier($data['queryData']['sid']);
        $data['hoSoOnline'] = $data['applier']->sessionGet(Applier::ET_HO_SO_ONLINE);
        if ($data['hoSoOnline']->phaiThanhToanQuaVietinBank()) {
            \Model\Entity\CongDan::forceLoginIfDoesNotDVC($this->getRequest()->getRequestUri());
        }
        if($data['hoSoOnline']->getSoHoSo()) { // CCCD: check có được cập nhật hay không
            $this->checkCanUpdateORDeleteCCCD($data['hoSoOnline']->getSoHoSo(), $data['queryData']['sid']);
        }
        $data['model']->checkUpdateRequirement($data['queryData']['sid']);
        $data['quyTrinhVilisXml'] = $data['applier']->sessionGet(Applier::XML_VILIS);
        $data['giayToVilis'] = $data['applier']->sessionGet(Applier::GT_VILIS);
        $data['hoSoVilis'] = $data['applier']->sessionGet(Applier::HS_VILIS);
        $data['lienThongVilis'] = $data['applier']->sessionGet(Applier::VILIS_ACTIVE);
        $data['dmLePhiHoSo'] = $data['hoSoOnline']->getDmLePhiHoSo();
        $data['qttt'] = $data['hoSoOnline']->getQttt();
        $data['thuTuc'] = $data['hoSoOnline']->getThuTuc();
        $data['donViTiepNhan'] = $data['hoSoOnline']->getDonViTiepNhan();
        $data['mucDo'] = $data['thuTuc']->getMucDo();
        $data['dmGiayToCuaHoSo'] = $data['hoSoOnline']->getDanhSachGiayToNop();
        $data['congDan'] = $data['hoSoOnline']->getCongDan();

        $MA_THU_TUC = $data['thuTuc']->getMaThuTuc();
        $tsqt_nop_hs_2b = Model\Entity\System\Parameter::fromId('BCA_CAU_HINH_TT_QUY_TRINH_NOP_HS_HAI_BUOC')->getValue();
        $tsqt_nop_hs_3b = Model\Entity\System\Parameter::fromId('BCA_CAU_HINH_TT_QUY_TRINH_NOP_HS_BA_BUOC')->getValue();
        $qt_nop_hs_2b = $tsqt_nop_hs_2b ? explode(',', $tsqt_nop_hs_2b) : [];
        $qt_nop_hs_3b = $tsqt_nop_hs_3b ? explode(',', $tsqt_nop_hs_3b) : [];
        if(in_array($MA_THU_TUC, $qt_nop_hs_2b) || in_array($MA_THU_TUC, $qt_nop_hs_3b)){
            $data['progress'] = new Progress($data['applier'], 3, 4);
        }else{
            $data['progress'] = new Progress($data['applier'], 5, 7);
        }

        // yêu cầu tài khoản login phải đã sso lên cổng quốc gia
        $this->checkSSOTKQUOCGIA($MA_THU_TUC);

        // Check nếu không phải số hồ sơ được cấu hình theo url BCA
        $this->checkQuyTrinhBCA($MA_THU_TUC);

        // check có phải thủ tục Khai báo tạm trú và tài khoản công dân BCA đã đăng nhập hay chưa
        //  => redirect trang đăng nhập riêng
        $this->checkThuTucKBTT($MA_THU_TUC, $this->getRequest()->getRequestUri(), true);

        $lbm = $data['hoSoOnline']->layMaEformID();

        if($lbm)
        {
            if($data['hoSoOnline']->getMaHoSo() == $data['queryData']['sid']) {
                $bmTDL = $this->getModel()->layDanhSachDuLieuTDL(null, $lbm, null, $data['hoSoOnline']->getMaHoSo())->getDefaultResult();
            } else {
                $bmTDL = $this->getModel()->layDanhSachDuLieuTDL($data['queryData']['sid'], $lbm, null,null)->getDefaultResult();
            }

            $cn = [];
            $val = [];
            $val_total = [];
            foreach ($bmTDL as $key => $value) {
                array_push($cn,$value['CONTROL_NAME']);
                array_push($val,$value['GIA_TRI']);
                $val_total[$value['CONTROL_NAME']] = [
                    'GIA_TRI' => $value["GIA_TRI"],
                    'CHECKED' => $value["CHECKED"],
                    'TYPE' => $value["TYPE"]
                ];
            }
            $data['controlName'] = $cn;
            $data['giaTri'] = $val;
            $data['giaTriBML'] = $val_total;
        }
        else
        {
            $data['controlName'] = null;
            $data['giaTri'] = null;
            $data['giaTriBML'] = null;
        }
        $data['bieuMau'] = array_combine($data['controlName'], $data['giaTri']);
        $data['lienThongBCA'] = false;
        $data['layLichQuaAPI'] = false;
        $tt_goi_lich_qua_api = Model\Entity\System\Parameter::fromId('BCA_TT_GOI_LICH_QUA_API')->getValue();
        $MA_THU_TUC = $data['thuTuc']->getMaThuTuc();

        $arr_tt_lich_api = $tt_goi_lich_qua_api ? explode(',', $tt_goi_lich_qua_api) : [];

        if(in_array($MA_THU_TUC, $arr_tt_lich_api)) {
            $key_search_dv = Entity\System\Parameter::fromId('KEY_BCA_IDS_MA_DON_VI', ["cache" => false])->getValue();

            if ($data['hoSoOnline']->getMaHoSo() == $data['queryData']['sid']) {
                $maDVTN = (new \Oracle\Package\BCA_DBLT())->SELECT_GIA_TRI_BY_MA_HO_SO([
                    'P_MA_TIEU_CHI_SEARCH' => !empty($key_search_dv) ? $key_search_dv : 'IDS_BCA_DON_VI_TIEP_NHAN_HS', //Mã đơn vị tiếp nhận IDS_BCA_NOI_NOP_HS
                    'P_SID' => $data['queryData']['sid'],
                ])[0];
            }
            else {
                $maDVTN = (new \Oracle\Package\BCA_DBLT())->SELECT_GIA_TRI_BY_MTC_SID([
                    'P_MA_TIEU_CHI_SEARCH' => !empty($key_search_dv) ? $key_search_dv : 'IDS_BCA_DON_VI_TIEP_NHAN_HS', //Mã đơn vị tiếp nhận IDS_BCA_NOI_NOP_HS
                    'P_SID' => $data['queryData']['sid'],
                ])[0];
            }

            $maDVTN = $maDVTN['GIA_TRI'];
            $maCoQuan = $data['hoSoOnline']->getMaCoQuan();
            $rs = $this->curl_lay_thong_tin_lich_hen($maDVTN);
            $rs = json_decode($rs, true);

            if ($rs['total'] > 0) {
                $arr_lich = $rs['rows'];
                $arr_ngay_lv = [];
                foreach ($arr_lich as $item) {
                    if ($item['QUOTA_LIMIT'] > $item['QUOTA_CURRENT'] || empty($item['QUOTA_CURRENT'])) {
                        $timestampWorkDay = strtotime(str_replace('/','-',$item['WORK_DAY']));
                        $timestampCurDay = strtotime(date('d-m-Y'));
                        $weekDay = date('w', $timestampWorkDay);
                        if ($timestampWorkDay > $timestampCurDay && $weekDay != 0) {
                            $arr_ngay_lv[] = $item['WORK_DAY'];
                        }
                    }
                }
                if (!empty($arr_ngay_lv)) {
                    $data['ngayLamViec'] = json_encode($arr_ngay_lv, JSON_FORCE_OBJECT);
                    $data['maDVTN'] = $maDVTN;
                    $data['maCoQuan'] = $maCoQuan;
                    $data['layLichQuaAPI'] = true;
                } else {
                    $data['layLichQuaAPI'] = false;
                }
            }
        } else if ((int) Entity\System\Parameter::fromId('BCA_SERVICE_ACTIVE')->getValue() === 1) {
            $key_ngay_hen = Entity\System\Parameter::fromId('KEY_BCA_IDS_NGAY_HEN')->getValue();
            $key_time_hen = Entity\System\Parameter::fromId('KEY_BCA_IDS_TIME_HEN')->getValue();
            if ($data['hoSoOnline']->getMaHoSo() == $data['queryData']['sid']) {
                $check_ngayhen = (new \Oracle\Package\BCA_DBLT())->CHECK_MTC_SEARCH_SAU_LUU([
                    'P_MA_TIEU_CHI_SEARCH' => (!empty($key_ngay_hen)) ? $key_ngay_hen : 'IDS_BCA_NGAY_HEN',//IDS_BCA_NGAY_HEN
                    'P_SID' => $data['queryData']['sid'],
                ]);

                $check_buoihen = (new \Oracle\Package\BCA_DBLT())->CHECK_MTC_SEARCH_SAU_LUU([
                    'P_MA_TIEU_CHI_SEARCH' => (!empty($key_time_hen)) ? $key_time_hen : 'IDS_BCA_TIME_HEN',//IDS_BCA_BUOI_HEN
                    'P_SID' => $data['queryData']['sid'],
                ]);
            } else {
                $check_ngayhen = (new \Oracle\Package\BCA_DBLT())->CHECK_MTC_SEARCH_TRUOC_LUU([
                    'P_MA_TIEU_CHI_SEARCH' => (!empty($key_ngay_hen)) ? $key_ngay_hen : 'IDS_BCA_NGAY_HEN',//IDS_BCA_NGAY_HEN
                    'P_SID' => $data['queryData']['sid'],
                ]);

                $check_buoihen = (new \Oracle\Package\BCA_DBLT())->CHECK_MTC_SEARCH_TRUOC_LUU([
                    'P_MA_TIEU_CHI_SEARCH' => (!empty($key_time_hen)) ? $key_time_hen : 'IDS_BCA_TIME_HEN',//IDS_BCA_BUOI_HEN
                    'P_SID' => $data['queryData']['sid'],
                ]);
            }

            if ($check_ngayhen == 1 && $check_buoihen == 1) {
                $key_search_dv = Entity\System\Parameter::fromId('KEY_BCA_IDS_MA_DON_VI', ["cache" => false])->getValue();

                if ($data['hoSoOnline']->getMaHoSo() == $data['queryData']['sid']) {
                    $maDVTN = (new \Oracle\Package\BCA_DBLT())->SELECT_GIA_TRI_BY_MA_HO_SO([
                        'P_MA_TIEU_CHI_SEARCH' => !empty($key_search_dv) ? $key_search_dv : 'IDS_BCA_DON_VI_TIEP_NHAN_HS', //Mã đơn vị tiếp nhận IDS_BCA_NOI_NOP_HS
                        'P_SID' => $data['queryData']['sid'],
                    ])[0];
                }
                else {
                    $maDVTN = (new \Oracle\Package\BCA_DBLT())->SELECT_GIA_TRI_BY_MTC_SID([
                        'P_MA_TIEU_CHI_SEARCH' => !empty($key_search_dv) ? $key_search_dv : 'IDS_BCA_DON_VI_TIEP_NHAN_HS', //Mã đơn vị tiếp nhận IDS_BCA_NOI_NOP_HS
                        'P_SID' => $data['queryData']['sid'],
                    ])[0];
                }

                $maDVTN = $maDVTN['GIA_TRI'];
                $maCoQuan = $data['hoSoOnline']->getMaCoQuan();

                $check_colich = (new \Oracle\Package\BCA_LIEN_THONG())->CHECK_CO_LICH_HEN_DV([
                    'P_MA_DON_VI' => $maDVTN,
                    'P_MA_CO_QUAN' => $maCoQuan,
                ]);

                if ($check_colich == 1) {
                    $ngay_gioi_han = (int) Entity\System\Parameter::fromId('BCA_GIOI_HAN_NGAY_HEN_NOP')->getValue() ?: 30;
                    $chuoi_ngay_nghi = (new \Oracle\Package\BCA_LICH_HEN())->SELECT_NGAY_NGHI_TRONG_KHOANG([
                        'P_MA_DON_VI' => $maDVTN,
                        'P_MA_CO_QUAN' => $maCoQuan,
                        'P_THAM_SO_NGAY' => $ngay_gioi_han
                    ]);

                    $arr_nn = $chuoi_ngay_nghi ? explode(',',$chuoi_ngay_nghi) : [];
                    $data['ngayNghi'] = json_encode(array_filter($arr_nn));
                    $data['maDVTN'] = $maDVTN;
                    $data['maCoQuan'] = $maCoQuan;
                    $data['ngayGioiHanHen'] = $ngay_gioi_han;
                    $data['lienThongBCA'] = true;
                }
            }
        }


        $this->getView()->render('xac-nhan-thong-tin-nop', $data);
    }

    public function xacNhanThongTinNopMoi() {
        $data['model'] = $this->getModel();
        $data['queryData'] = $data['model']->getQueryData();
        $data['model']->checkUpdateRequirement($data['queryData']['sid']);
        $data['applier'] = new Applier($data['queryData']['sid']);
        $data['gopBuocNopHS'] = (int) Entity\System\Parameter::fromId('DVC_SO_BUOC_NOP_HO_SO')->getValue();

        $data['progress'] = new Progress($data['applier'], 5, 7);

        $data['hoSoOnline'] = $data['applier']->sessionGet(Applier::ET_HO_SO_ONLINE);

        if ($data['hoSoOnline']->phaiThanhToanQuaVietinBank()) {
            \Model\Entity\CongDan::forceLoginIfDoesNotDVC($this->getRequest()->getRequestUri());
        }

        $data['quyTrinhVilisXml'] = $data['applier']->sessionGet(Applier::XML_VILIS);
        $data['giayToVilis'] = $data['applier']->sessionGet(Applier::GT_VILIS);
        $data['hoSoVilis'] = $data['applier']->sessionGet(Applier::HS_VILIS);
        $data['lienThongVilis'] = $data['applier']->sessionGet(Applier::VILIS_ACTIVE);
        $data['dmLePhiHoSo'] = $data['hoSoOnline']->getDmLePhiHoSo();
        $data['qttt'] = $data['hoSoOnline']->getQttt();
        $data['thuTuc'] = $data['hoSoOnline']->getThuTuc();
        $data['donViTiepNhan'] = $data['hoSoOnline']->getDonViTiepNhan();
        $data['mucDo'] = $data['thuTuc']->getMucDo();
        $data['dmGiayToCuaHoSo'] = $data['hoSoOnline']->getDanhSachGiayToNop();
        $data['congDan'] = $data['hoSoOnline']->getCongDan();
        if((int) Entity\System\Parameter::fromId('DOI_TUONG_GIAM_CUOC_VNPOST')->getValue() === 1) { //IGATESUPP-26470 tttruong-kv1
            $data['dtMienGiamCuocVNPost'] = json_decode($data['applier']->sessionGet('DT_MG_VNPOST'),true);
        }
        $lbm = $data['hoSoOnline']->layMaEformID();

        if($lbm)
        {
            if(!empty($data['hoSoOnline']) && !empty($data['hoSoOnline']->getMaHoSo()) && $data['queryData']['sid'] == $data['hoSoOnline']->getMaHoSo()) {
                $bmTDL = $this->getModel()->layDanhSachDuLieuTDL(null, $lbm, null, $data['queryData']['sid'])->getDefaultResult();
            } else {
                $bmTDL = $this->getModel()->layDanhSachDuLieuTDL($data['queryData']['sid'], $lbm, null, null)->getDefaultResult();
            }
            $cn = [];
            $val = [];
            $val_total = [];
            foreach ($bmTDL as $key => $value) {
                array_push($cn,$value['CONTROL_NAME']);
                array_push($val,$value['GIA_TRI']);
                $val_total[$value['CONTROL_NAME']] = [
                    'GIA_TRI' => $value["GIA_TRI"],
                    'CHECKED' => $value["CHECKED"],
                    'TYPE' => $value["TYPE"]
                ];
            }
            $data['controlName'] = $cn;
            $data['giaTri'] = $val;
            $data['giaTriBML'] = $val_total;
        }
        else
        {
            $data['controlName'] = null;
            $data['giaTri'] = null;
            $data['giaTriBML'] = null;
        }

        // Check nếu không phải số hồ sơ được cấu hình theo url BCA
        $MA_THU_TUC = $data['thuTuc']->getMaThuTuc();

        // yêu cầu tài khoản login phải đã sso lên cổng quốc gia
        $this->checkSSOTKQUOCGIA($MA_THU_TUC);

        $this->checkQuyTrinhBCA($MA_THU_TUC);

        // check có phải thủ tục Khai báo tạm trú và tài khoản công dân BCA đã đăng nhập hay chưa
        //  => redirect trang đăng nhập riêng
        $this->checkThuTucKBTT($MA_THU_TUC, $this->getRequest()->getRequestUri(), true);

        $this->getView()->render('xac-nhan-thong-tin-nop-moi', $data);
    }

    public function api_get_json_lich($maDonVi) {
        echo $this->curl_lay_thong_tin_lich_hen($maDonVi);
    }
    
    public function curl_lay_thong_tin_lich_hen($maDonVi) {
        if (Entity\System\Parameter::fromId('BCA_CHO_PHEP_TEST_LICH_API')->getValue() == 1) {
            return '{"total":6,"rows":[{"AGENCY_ID":null,"WORK_DAY":"20/11/2021","STATE":"","MORNING_START":"08:00","AFTERNOON_START":"13:30","SYNC_ID":"G01.001.012.000_cdf0dd3a-a8d3-4c35-9e79-35c06d13672c","MORNING_END":"11:30","STT":1,"QUOTA_LIMIT":100,"QUOTA_CURRENT":4,"ID":1011,"AGENCY_CODE":"G01.001.012.000","AFTERNOON_END":"17:00"},{"AGENCY_ID":null,"WORK_DAY":"14/11/2021","STATE":"","MORNING_START":"08:00","AFTERNOON_START":"13:30","SYNC_ID":"G01.001.012.000_eefb01ef-d4ff-440f-a802-2dae058c9709","MORNING_END":"11:30","STT":2,"QUOTA_LIMIT":100,"QUOTA_CURRENT":null,"ID":1009,"AGENCY_CODE":"G01.001.012.000","AFTERNOON_END":"17:00"},{"AGENCY_ID":null,"WORK_DAY":"02/10/2021","STATE":"","MORNING_START":"07:30","AFTERNOON_START":"13:30","SYNC_ID":"G01.001.012.000_5ed15247-801e-4c44-ae87-2feb7a63a247","MORNING_END":"11:30","STT":3,"QUOTA_LIMIT":60,"QUOTA_CURRENT":null,"ID":1019,"AGENCY_CODE":"G01.001.012.000","AFTERNOON_END":"18:00"},{"AGENCY_ID":null,"WORK_DAY":"30/09/2021","STATE":"","MORNING_START":"08:00","AFTERNOON_START":"13:30","SYNC_ID":"G01.001.012.000_cdf0dd3a-a8d3-4c35-9e79-35c06d13672c","MORNING_END":"11:30","STT":4,"QUOTA_LIMIT":100,"QUOTA_CURRENT":null,"ID":993,"AGENCY_CODE":"G01.001.012.000","AFTERNOON_END":"17:00"},{"AGENCY_ID":null,"WORK_DAY":"29/09/2021","STATE":"","MORNING_START":"08:00","AFTERNOON_START":"13:30","SYNC_ID":"G01.001.012.000_886cfd60-58b9-43cd-8365-a8fe663b0295","MORNING_END":"11:30","STT":5,"QUOTA_LIMIT":100,"QUOTA_CURRENT":null,"ID":991,"AGENCY_CODE":"G01.001.012.000","AFTERNOON_END":"17:00"},{"AGENCY_ID":null,"WORK_DAY":"03/10/2021","STATE":"","MORNING_START":"07:30","AFTERNOON_START":"13:30","SYNC_ID":"G01.001.012.000_2318b253-989c-43b9-a4f3-0aa42c765009","MORNING_END":"11:30","STT":6,"QUOTA_LIMIT":150,"QUOTA_CURRENT":null,"ID":1037,"AGENCY_CODE":"G01.001.012.000","AFTERNOON_END":"21:00"}]}';
        } else {
            $url = Entity\System\Parameter::fromId('BCA_URL_GET_LICH_CCCD')->getValue();
            $url = !empty($url) ? $url : 'http://10.159.29.18:7004/portalapi_ext/callsvc?client_id=get_data_categories_cccd';
            $data = [
                "p_agency_id" => $maDonVi,
                "provider" => "dancuportal",
                "type" => "json",
                "service" => "get_calender_work_day"
            ];
            $token = Entity\System\Parameter::fromId('BCA_TOKEN_LICH_HEN_CCCD')->getValue();
            $token = !empty($token) ? $token : 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJzdWIiOiJnZXRfZGF0YV9jYXRlZ29yaWVzX2NjY2QiLCJpc3MiOiJPQXV0aCJ9.2IxTqvXMaK0E0KHW_cJO_kwBxY6VwXm1etAsTeG5bgTPUxpTcgLwzXbOhO7FxUMHJfoeo6okOEaRBGjguKbTMQ';
            $header = [
                'Content-Type: application/json',
                'Accept: application/json',
                'Access-Control-Allow-Headers: Content-Type',
                $token
            ];
            $data_string = json_encode($data);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6');
            $rs = curl_exec($ch);
            curl_close($ch);
            return $rs;
        }
    }

    public function curl_check_ngay_lam_viec() {
        if (Entity\System\Parameter::fromId('BCA_CHO_PHEP_TEST_LICH_API')->getValue() == 1) {
            echo '{"VAL":"","MSG_TEXT":"Thời gian được phép nộp hồ sơ","MSG_CODE":"OK"}';
        } else {
            $url = Entity\System\Parameter::fromId('BCA_URL_CHECK_LICH_CCCD')->getValue();
            $url = !empty($url) ? $url : 'http://10.159.29.18:7004/portalapi_ext/callsvc?client_id=get_data_categories_cccd';
            $P_NGAY_HEN = get_post_var('P_NGAY_HEN');
            $P_MA_DON_VI = get_post_var('P_MA_DON_VI');
            $data = [
                "p_receive_result_day" => $P_NGAY_HEN,
                "p_agency_id" => $P_MA_DON_VI,
                "provider" => "dancuportal",
                "type" => "json",
                "service" => "check_dossier_quota_one_day"
            ];
            $token = Entity\System\Parameter::fromId('BCA_TOKEN_LICH_HEN_CCCD')->getValue();
            $token = !empty($token) ? $token : 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJzdWIiOiJnZXRfZGF0YV9jYXRlZ29yaWVzX2NjY2QiLCJpc3MiOiJPQXV0aCJ9.2IxTqvXMaK0E0KHW_cJO_kwBxY6VwXm1etAsTeG5bgTPUxpTcgLwzXbOhO7FxUMHJfoeo6okOEaRBGjguKbTMQ';
            $header = [
                'Content-Type: application/json',
                'Accept: application/json',
                'Access-Control-Allow-Headers: Content-Type',
                $token
            ];
            $data_string = json_encode($data);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6');
            $rs = curl_exec($ch);
            curl_close($ch);
            echo $rs;
        }
  
    }

    public function xacNhanThongTinNop3b() {
        $data['model'] = $this->getModel();
        $data['queryData'] = $data['model']->getQueryData();
        $data['model']->checkUpdateRequirement($data['queryData']['sid']);
        $data['applier'] = new Applier($data['queryData']['sid']);
        $data['progress'] = new Progress($data['applier'], 4,5);
        $data['hoSoOnline'] = $data['applier']->sessionGet(Applier::ET_HO_SO_ONLINE);

        if ($data['hoSoOnline']->phaiThanhToanQuaVietinBank()) {
            \Model\Entity\CongDan::forceLoginIfDoesNotDVC($this->getRequest()->getRequestUri());
        }

        $data['quyTrinhVilisXml'] = $data['applier']->sessionGet(Applier::XML_VILIS);
        $data['giayToVilis'] = $data['applier']->sessionGet(Applier::GT_VILIS);
        $data['hoSoVilis'] = $data['applier']->sessionGet(Applier::HS_VILIS);
        $data['lienThongVilis'] = $data['applier']->sessionGet(Applier::VILIS_ACTIVE);
        $data['dmLePhiHoSo'] = $data['hoSoOnline']->getDmLePhiHoSo();
        $data['qttt'] = $data['hoSoOnline']->getQttt();
        $data['thuTuc'] = $data['hoSoOnline']->getThuTuc();
        $data['donViTiepNhan'] = $data['hoSoOnline']->getDonViTiepNhan();
        $data['mucDo'] = $data['thuTuc']->getMucDo();
        $data['dmGiayToCuaHoSo'] = $data['hoSoOnline']->getDanhSachGiayToNop();
        $data['congDan'] = $data['hoSoOnline']->getCongDan();
        $lbm = $data['hoSoOnline']->layMaEformID();

        if($lbm)
        {
            if(!empty($data['hoSoOnline']) && !empty($data['hoSoOnline']->getMaHoSo()) && $data['queryData']['sid'] == $data['hoSoOnline']->getMaHoSo()) {
                $bmTDL = $this->getModel()->layDanhSachDuLieuTDL(null, $lbm, null, $data['queryData']['sid'])->getDefaultResult();
            } else {
                $bmTDL = $this->getModel()->layDanhSachDuLieuTDL($data['queryData']['sid'], $lbm, null, null)->getDefaultResult();
            }
            $cn = [];
            $val = [];
            $val_total = [];
            foreach ($bmTDL as $key => $value) {
                array_push($cn,$value['CONTROL_NAME']);
                array_push($val,$value['GIA_TRI']);
                $val_total[$value['CONTROL_NAME']] = [
                    'GIA_TRI' => $value["GIA_TRI"],
                    'CHECKED' => $value["CHECKED"],
                    'TYPE' => $value["TYPE"]
                ];
            }
            $data['controlName'] = $cn;
            $data['giaTri'] = $val;
            $data['giaTriBML'] = $val_total;
        }
        else
        {
            $data['controlName'] = null;
            $data['giaTri'] = null;
            $data['giaTriBML'] = null;
        }

        // Check nếu không phải số hồ sơ được cấu hình theo url BCA
        $MA_THU_TUC = $data['thuTuc']->getMaThuTuc();

        // yêu cầu tài khoản login phải đã sso lên cổng quốc gia
        $this->checkSSOTKQUOCGIA($MA_THU_TUC);

        $this->checkQuyTrinhBCA($MA_THU_TUC);

        // check có phải thủ tục Khai báo tạm trú và tài khoản công dân BCA đã đăng nhập hay chưa
        //  => redirect trang đăng nhập riêng
        $this->checkThuTucKBTT($MA_THU_TUC, $this->getRequest()->getRequestUri(), true);

        $this->getView()->render('xac-nhan-thong-tin-nop-3b', $data);
    }

    public function check_file_exist() {
        $fileName = get_post_var('fileName', '', false);
        $check = '';
        if($fileName){
            for ($i=0; $i < count($fileName) ; $i++) {
                $file = $fileName[$i];
                $urlFile = preg_replace('/[^A-Za-z0-9_.\s\/]/', '', $file);
                if(!file_exists($urlFile)){
                    $check = $file;
                }
            }
        }
        echo json_encode([
            "result" => $check
        ]);
    }
    public function luuHoSo() {
        $this->getModel()->luuHoSo();
    }

    public function nopHoSo() {
        $this->getModel()->nopHoSo();
    }

    public function ketQuaGiaoDichSmartGate() {
        $this->getModel()->ketQuaGiaoDichSmartGate();
    }

    public function huyGiaoDichSmartGate() {
        $this->getModel()->huyGiaoDichSmartGate();
    }

    public function vnptpayPaymentSuccess() {
        $this->getModel()->vnptpayPaymentSuccess();
    }

    public function payGovSuccess() {
        $this->getModel()->payGovSuccess();
    }

    public function daNopHoSo() {
        $data['model'] = $this->getModel();
        $data['queryData'] = $data['model']->getQueryData();
        $data['model']->checkViewRequirement($data['queryData']['sid']);
        $data['applier'] = new Applier($data['queryData']['sid']);
        $data['progress'] = new Progress($data['applier'], null, 4);
        $data['hoSoOnline'] = $data['applier']->sessionGet(Applier::ET_HO_SO_ONLINE);
        $data['congDan'] = $data['hoSoOnline']->getCongDan();
        $data['webId'] = \Model\Entity\System\Parameter::fromId('WEB_ID_THEO_DOI_DVCTT')->getValue();

        if ($data['webId'] != 0) {
            $theoDoiThuTuc = (new \Oracle\StoreProcedure\SELECT_TT_THEO_DOI_THU_TUC([
                'P_MA_CO_QUAN' => $data['hoSoOnline']->getMaCoQuan(),
                'P_MA_THU_TUC' => $data['hoSoOnline']->getMaThuTuc()
            ]))->getDefaultResult();
            $data['activeTheoDoiDVCTT'] = count($theoDoiThuTuc);
            if ($data['activeTheoDoiDVCTT'] > 0) {
                $maMucDo = $data['hoSoOnline']->getThuTuc()->getMucDo()->getMaMucDo();
                $data['lever'] = 3;
                if ($maMucDo == 'MUC_DO_4') {
                    $data['lever'] = 4;
                } elseif ($maMucDo == 'MUC_DO_3') {
                    $data['lever'] = 3;
                } elseif ($maMucDo == 'MUC_DO_2') {
                    $data['lever'] = 2;
                } elseif ($maMucDo == 'MUC_DO_1') {
                    $data['lever'] = 1;
                }
                $data['sBody'] = '';
                if ($data['hoSoOnline']->getCongDan()->getTenCongDan() != '' && !empty($data['hoSoOnline']->getCongDan()->getTenCongDan())) {
                    $data['sBody'] = $data['sBody'] . 'Họ Tên: ' . $data['hoSoOnline']->getCongDan()->getTenCongDan() . ';';
                }
                if ($data['hoSoOnline']->getCongDan()->getEmail() != '' && !empty($data['hoSoOnline']->getCongDan()->getEmail())) {
                    $data['sBody'] = $data['sBody'] . 'Email: ' . $data['hoSoOnline']->getCongDan()->getEmail() . ';';
                }
                if ($data['hoSoOnline']->getCongDan()->getSoCmnd() != '' && !empty($data['hoSoOnline']->getCongDan()->getSoCmnd())) {
                    $data['sBody'] = $data['sBody'] . 'Cmnd: ' . $data['hoSoOnline']->getCongDan()->getSoCmnd() . ';';
                }
                if ($data['hoSoOnline']->getTenCongDanNop() != '' && !empty($data['hoSoOnline']->getTenCongDanNop())) {
                    $data['sBody'] = $data['sBody'] . 'Họ Tên: ' . $data['hoSoOnline']->getTenCongDanNop() . ';';
                }
                if ($data['hoSoOnline']->getDiDongLienLacCongDan() != '' && !empty($data['hoSoOnline']->getDiDongLienLacCongDan())) {
                    $data['sBody'] = $data['sBody'] . 'Di Động: ' . $data['hoSoOnline']->getDiDongLienLacCongDan() . ';';
                }
                if ($data['hoSoOnline']->getEmailCongDan() != '' && !empty($data['hoSoOnline']->getEmailCongDan())) {
                    $data['sBody'] = $data['sBody'] . 'Email: ' . $data['hoSoOnline']->getEmailCongDan() . ';';
                }
                $data['nameTt'] = $data['hoSoOnline']->getThuTuc()->getTenThuTuc();
            }
        }
        $data['applier']->sessionUnset(Applier::ET_HO_SO_ONLINE);
        $data['applier']->sessionUnset(Applier::ER_VILIS);
        $data['applier']->sessionUnset(Applier::GT_VILIS);
        $data['applier']->sessionUnset(Applier::HS_VILIS);
        $data['DS_MA_TIEP_NHAN'] = $this->model->hososingle->SELECT_TT_PHIEU_TIEP_NHAN_ONL(null,$data['hoSoOnline']->getMaHoSo());
        $this->getView()->render('da-nop-ho-so', $data);
    }

    public function thanhToanLePhiThatBai() {
        $data['model'] = $this->getModel();
        $data['queryData'] = $data['model']->getQueryData();
        $data['model']->checkUpdateRequirement($data['queryData']['sid']);
        if ((new Csrf('tttb'))->isValid($data['queryData']['token'])) {
            $data['applier'] = new Applier($data['queryData']['sid']);
            $data['hoSoOnline'] = $data['applier']->sessionGet(Applier::ET_HO_SO_ONLINE);
            return $this->getView()->render('thanh-toan-le-phi-that-bai', $data);
        }
        exit('Site does not exists or time expired');
    }

    public function daLuuHoSo() {
        $data['model'] = $this->getModel();
        $data['queryData'] = $data['model']->getQueryData();
        $data['model']->checkViewRequirement($data['queryData']['sid']);
        $data['applier'] = new Applier($data['queryData']['sid']);
        $data['progress'] = new Progress($data['applier'], null, 4);
        $data['hoSoOnline'] = $data['applier']->sessionGet(Applier::ET_HO_SO_ONLINE);
        $data['congDan'] = $data['hoSoOnline']->getCongDan();
        $this->getView()->render('da-luu-ho-so', $data);
    }

    public function xacNhanMaNopHoSo() {
        $data['model'] = $this->getModel();
        $data['queryData'] = $data['model']->getQueryData();
        $data['currentUrl'] = $this->getRequest()->getRequestUri();
        $this->getView()->render('ma-nop-ho-so', $data);
    }

    public function kiemTraMaNopHoSo() {
        $this->getModel()->kiemTraMaNopHoSo();
    }

    public function guiLaiMaNopHoSo() {
        $this->getModel()->guiLaiMaNopHoSo();
    }

    // Cho trường hợp nộp hồ sơ 3 bước
    public function nhapThongTinHoSo() {
        $data['model'] = $this->getModel();
        $data['queryData'] = $data['model']->getQueryData();
        $data['applier'] = new Applier($data['queryData']['sid']);
        $data['hoSoOnline'] = $data['applier']->sessionGet(Applier::ET_HO_SO_ONLINE);
        if($data['hoSoOnline']->getSoHoSo()) { // CCCD: check có được cập nhật hay không
            $this->checkCanUpdateORDeleteCCCD($data['hoSoOnline']->getSoHoSo(), $data['queryData']['sid']);
        }
        $data['model']->checkUpdateRequirement($data['queryData']['sid']);
        $data['qttt'] = $data['hoSoOnline']->getQttt();
        $data['thuTuc'] = $data['hoSoOnline']->getThuTuc();
        $data['congDan'] = $data['hoSoOnline']->getCongDan();
        $data['dmGiayToCuaHoSo'] = $data['hoSoOnline']->getDanhSachGiayToNop();
        $data['donViTiepNhan'] = $data['hoSoOnline']->getDonViTiepNhan();
        $data['mucDo'] = $data['thuTuc']->getMucDo();
        $data['congViecTiepNhan'] = $data['qttt']->layCongViecTiepNhan();
        $data['hoSoVilis'] = Entity\HoSoVilis::fromMaHoSoOnline($data['hoSoOnline']->getMaHoSo(), ['lre_options' => ['PhuongXa' => ['QuanHuyen']]]);
        $lbm = $data['hoSoOnline']->layMaEformID();
        $maTruongHopCuTruC06 = '';
        if($lbm)
        {
            $bmTDL = $this->getModel()->layDanhSachDuLieuTDL($data['queryData']['sid'], $lbm, null,null)->getDefaultResult();
            foreach ($bmTDL as $key => $value) {
                if($value['CONTROL_NAME'] == '_fs_BCATruongHop') {
                    $maTruongHopCuTruC06 = $value['GIA_TRI'];
                }
            }
       
        }
        if ($maTruongHopCuTruC06 === '') { // lay danh sach giay to nhu cu
            $data['dmGiayToCuaThuTuc'] = new DanhMuc\GiayToCuaThuTuc([
                'maThuTuc' => $data['thuTuc']->getMaThuTuc(),
                'maCongViecQttt' => $data['congViecTiepNhan'] ? $data['congViecTiepNhan']->getMaCongViecQttt() : null,
                'loaiGiayToThaoTac' => 0,
                'trangThai' => 0
            ]);
        } else { // lay moi
            $data['dmGiayToCuaThuTuc'] = new DanhMuc\GiayToCuaThuTucBca([
                'maThuTuc' => $data['thuTuc']->getMaThuTuc(),
                'maCongViecQttt' => $data['congViecTiepNhan'] ? $data['congViecTiepNhan']->getMaCongViecQttt() : null,
                'loaiGiayToThaoTac' => 0,
                'trangThai' => 0,
                'maTruongHopCuTruC06' => $maTruongHopCuTruC06
            ]);
        }
       
        $data['lienThongHoSoHTK'] = 0;
        $data['sid'] = $data['queryData']['sid'];
        if (empty($data['hoSoVilis']->getMaHoSoVilis())) {
            $vilisFlag = (int)Entity\System\Parameter::fromId('LIEN_THONG_VILIS')->getValue();
            $data['maQuanHuyenThuaDat'] =  $data['applier']->sessionGet(Applier::VILIS_MA_QUAN_HUYEN_THUA_DAT);
        } else {
            $vilisFlag = 1;
            $data['maQuanHuyenThuaDat'] = $data['hoSoVilis']->getPhuongXa()->getQuanHuyen()->getMaQuanHuyen();
        }
        $vilisLuuGiayToDinhKem = (int)Entity\System\Parameter::fromId('vilis_LuuGiayToDinhKem')->getValue();
        $maViLis = $data['model']->getMaVilis($data['qttt']->getMaQttt(),$data['maQuanHuyenThuaDat']);
        $data['lienThongVilis'] = (int)($vilisFlag && $maViLis);
        $data['formGiayToVilis'] = (int)($vilisFlag && $maViLis && $vilisLuuGiayToDinhKem);
        $data['applier']->sessionSet(Applier::VILIS_ACTIVE, $data['lienThongVilis']);
        if ($lths = Entity\System\Parameter::fromId('DV_LIEN_THONG_HO_SO')->getValue()) {
            $ttlt = Entity\ThuTucLienThong::fromMaThuTuc($data['thuTuc']->getMaThuTuc());
            if ($lths == 'DAKLAK' && $ttlt->getLinkWs() != '') {
                $data['lienThongHoSoHTK'] = 'DAKLAK';
            }
        }
        $data['choPhepHienThiTPHSKhac'] = (new Oracle\StoreProcedure\SELECT_TT_CAU_HINH_QUY_TRINH([
                                           'P_MA_THU_TUC' => $data['thuTuc']->getMaThuTuc(),
                                           'P_MA_QTTT' => $data['qttt']->getMaQttt()]))->getDefaultResult();
        //check exist file on server
        foreach ($data['dmGiayToCuaHoSo']->getItems() as $giayto) {
            $file_path = $this->model->filterPath($giayto->getFileGiayTo());
            if(!file_exists($file_path)){
                $giayto->setFileGiayTo('');
            }
        }
        $data['lienThongBCA'] = false;
        if ((int) Entity\System\Parameter::fromId('BCA_SERVICE_ACTIVE')->getValue() === 1) {
            $key_ngay_hen = Entity\System\Parameter::fromId('KEY_BCA_IDS_NGAY_HEN')->getValue();
            $key_time_hen = Entity\System\Parameter::fromId('KEY_BCA_IDS_TIME_HEN')->getValue();
            $check_ngayhen = (new \Oracle\Package\BCA_DBLT())->CHECK_MTC_SEARCH_TRUOC_LUU([
                'P_MA_TIEU_CHI_SEARCH' => (!empty($key_ngay_hen)) ? $key_ngay_hen : 'IDS_BCA_NGAY_HEN',//IDS_BCA_NGAY_HEN
                'P_SID' => $data['sid'],
            ]);
            $check_buoihen = (new \Oracle\Package\BCA_DBLT())->CHECK_MTC_SEARCH_TRUOC_LUU([
                'P_MA_TIEU_CHI_SEARCH' => (!empty($key_time_hen)) ? $key_time_hen : 'IDS_BCA_TIME_HEN',//IDS_BCA_BUOI_HEN
                'P_SID' => $data['sid'],
            ]);

            if ($check_ngayhen == 1 && $check_buoihen == 1) {
                $key_search_dv = Entity\System\Parameter::fromId('KEY_BCA_IDS_MA_DON_VI', ["cache" => false])->getValue();
                $maDVTN = (new \Oracle\Package\BCA_DBLT())->SELECT_GIA_TRI_BY_MTC_SID([
                    'P_MA_TIEU_CHI_SEARCH' => !empty($key_search_dv) ? $key_search_dv : 'IDS_BCA_DON_VI_TIEP_NHAN_HS', //Mã đơn vị tiếp nhận IDS_BCA_NOI_NOP_HS
                    'P_SID' => $data['sid'],
                ])[0];

                $maDVTN = $maDVTN['GIA_TRI'];
                $maCoQuan = $data['hoSoOnline']->getMaCoQuan();

                $check_colich = (new \Oracle\Package\BCA_LIEN_THONG())->CHECK_CO_LICH_HEN_DV([
                    'P_MA_DON_VI' => $maDVTN,
                    'P_MA_CO_QUAN' => $maCoQuan,
                ]);
                if ($check_colich == 1) {
                    $ngay_gioi_han = (int) Entity\System\Parameter::fromId('BCA_GIOI_HAN_NGAY_HEN_NOP')->getValue() ?: 30;
                    $chuoi_ngay_nghi = (new \Oracle\Package\BCA_LICH_HEN())->SELECT_NGAY_NGHI_TRONG_KHOANG([
                        'P_MA_DON_VI' => $maDVTN,
                        'P_MA_CO_QUAN' => $maCoQuan,
                        'P_THAM_SO_NGAY' => $ngay_gioi_han
                    ]);
                    $arr_nn = $chuoi_ngay_nghi ? explode(',',$chuoi_ngay_nghi) : [];
                    $data['ngayNghi'] = json_encode(array_filter($arr_nn));
                    $data['maDVTN'] = $maDVTN;
                    $data['maCoQuan'] = $maCoQuan;
                    $data['ngayGioiHanHen'] = $ngay_gioi_han;
                    $data['lienThongBCA'] = true;
                }
            }

        }

        // Check mã thủ tục có được cấu hình theo quy trình nộp hồ sơ BCA
        $MA_THU_TUC = $data['thuTuc']->getMaThuTuc();
        $tsqt_nop_hs_2b = Model\Entity\System\Parameter::fromId('BCA_CAU_HINH_TT_QUY_TRINH_NOP_HS_HAI_BUOC')->getValue();
        $tsqt_nop_hs_3b = Model\Entity\System\Parameter::fromId('BCA_CAU_HINH_TT_QUY_TRINH_NOP_HS_BA_BUOC')->getValue();
        $qt_nop_hs_2b = $tsqt_nop_hs_2b ? explode(',', $tsqt_nop_hs_2b) : [];
        $qt_nop_hs_3b = $tsqt_nop_hs_3b ? explode(',', $tsqt_nop_hs_3b) : [];

        if(in_array($MA_THU_TUC, $qt_nop_hs_2b) || in_array($MA_THU_TUC, $qt_nop_hs_3b)){
            $data['progress'] = new Progress($data['applier'], 3, 5);
        }else{
            $data['progress'] = new Progress($data['applier'], 3, 7);
        }

        // yêu cầu tài khoản login phải đã sso lên cổng quốc gia
        $this->checkSSOTKQUOCGIA($MA_THU_TUC);

        $this->checkQuyTrinhBCA($MA_THU_TUC);

        // check có phải thủ tục Khai báo tạm trú và tài khoản công dân BCA đã đăng nhập hay chưa
        //  => redirect trang đăng nhập riêng
        $this->checkThuTucKBTT($MA_THU_TUC, $this->getRequest()->getRequestUri(), true);

        $this->getView()->render('thong-tin-ho-so-nop', $data);
    }

    public function layBuoiLamViec() {
        $P_NGAY_HEN = get_post_var('P_NGAY_HEN');
        $P_MA_DON_VI = get_post_var('P_MA_DON_VI');
        $P_MA_CO_QUAN = get_post_var('P_MA_CO_QUAN');
        $P_THU = get_post_var('P_THU');
        $list_time = (new \Oracle\Package\BCA_LICH_HEN())->SELECT_TIME_LV_TRONG_NGAY([
            'P_MA_DON_VI' => $P_MA_DON_VI,
            'P_MA_CO_QUAN' => $P_MA_CO_QUAN,
            'P_THU' => $P_THU,
            'P_NGAY_HEN' => $P_NGAY_HEN
        ]);
        if ($list_time->count() == 0) {
            echo '<p class="form-error">Không thể đặt lịch vào ngày này! vui lòng chọn lại !!!</p>';
        } else {
            $html_select = '<label>Thời gian hẹn</label><select name="radio-buoi-hen" id="TIME_SLOT_HEN" class="form-control">';
            foreach ($list_time as $item) {
                $html_select .= '<option value="' . $item['TIME_SLOT'] . '"> ' . $item['MO_TA_TIME_SLOT'] . '</option>';
            }
            $html_select .= '</select>';
            echo $html_select;
        }
    }

    public function huyHoSo() {
        $data['model'] = $this->getModel();
        $data['queryData'] = $data['model']->getQueryData();
        $data['model']->checkViewRequirement($data['queryData']['sid']);
        //fix bug security
        $csrf = new Csrf();
        if($csrf->isValid($data['queryData']['token'])){
            $session = $csrf->getSession();
            $session->getManager()->getStorage()->clear($csrf->getSessionName());
        }else{
            exit('Permission denied');
        }
        //end
        $data['applier'] = new Applier($data['queryData']['sid']);
        $data['progress'] = new Progress($data['applier'], null, 4);
        $data['hoSoOnline'] = $data['applier']->sessionGet(Applier::ET_HO_SO_ONLINE);
        if($data['hoSoOnline']->getSoHoSo()) { // CCCD: check có được cập nhật hay không
            $this->checkCanUpdateORDeleteCCCD($data['hoSoOnline']->getSoHoSo(), $data['queryData']['sid']);
        }
        $data['resultInfo'] = $data['hoSoOnline']->huyHoSo(true);
        if ($data['resultInfo']->getCode() == 1) {
            // 2018.07.04 log huy ho so online -- 0003

            $dataLog = [];
            $dataLog = [
                'maHoSo'   => $data['hoSoOnline']->getMaHoSo(),
                'maCoQuan' => $data['hoSoOnline']->getMaCoQuan(),
                'maThuTuc' => $data['hoSoOnline']->getMaThuTuc(),
                'soHoSo'   => $data['hoSoOnline']->getSoHoSo()
            ];
            $package = new LogData\Package();
            $content = new LogData\Content();
            $package->setContent($content->createContentHuyHoSoOnline($dataLog, '0003'));
            $package->setContentId($data['hoSoOnline']->getMaHoSo());
            $package->setDescription('Công dân hủy hồ sơ online');
            $package->setDaLenTruc(0);
            $package->setPackageTypeId('0003');
            $package->update();

            $this->updateHuyCaThi($data['hoSoOnline']->getSoHoSo());
        }

        $this->getView()->render('huy-ho-so', $data);
    }

    public function chiTietHoSo() {
        $data['model'] = $this->getModel();
        $data['queryData'] = $data['model']->getQueryData();
        $data['model']->checkViewRequirement($data['queryData']['sid']);
        $data['applier'] = new Applier($data['queryData']['sid']);
        $data['progress'] = new Progress($data['applier'], null, 4);
        $sessionHoSo = $data['applier']->sessionGet(Applier::ET_HO_SO_ONLINE);

        $data['hoSoOnline'] = Entity\HoSoOnline::fromMaHoSo($sessionHoSo->getMaHoSo());
        if (!$data['hoSoOnline']->exists()) {
            $data['hoSoOnline'] = Entity\HoSo::fromMaHoSo($sessionHoSo->getMaHoSo());
        }

        if (!$data['hoSoOnline']->exists()) {
            exit('Hồ sơ không tồn tại!');
        }

        $data['hoSoOnline']->laore();

        $data['dmLePhiHoSo'] = $data['hoSoOnline']->getDmLePhiHoSo();
        $data['qttt'] = $data['hoSoOnline']->getQttt();
        $data['thuTuc'] = $data['hoSoOnline']->getThuTuc();
        $data['donViTiepNhan'] = $data['hoSoOnline']->getDonViTiepNhan();
        $data['mucDo'] = $data['thuTuc']->getMucDo();
        $data['dmGiayToCuaHoSo'] = $data['hoSoOnline']->getDanhSachGiayToNop();
        $data['congDan'] = $data['hoSoOnline']->getCongDan();
        $data['hoSoEntity'] = Entity\HoSo::fromMaHoSoOnline($sessionHoSo->getMaHoSo());
        $data['dmGiayToBS'] = (new Oracle\StoreProcedure\SELECT_HS_CO_GIAY_TO_BS([
                                'P_MA_HO_SO_ONLINE' => $data['queryData']['sid'],
                                'P_MA_CO_GIAY_TO_KHAC' => null,
                                'P_MA_HO_SO' => null
                              ]))->getDefaultResult();

        $lbm = $data['hoSoOnline']->layMaEformID();

        if($lbm)
        {
            if(!empty($data['hoSoOnline']) && !empty($data['hoSoOnline']->getMaHoSo()) && $data['queryData']['sid'] == $data['hoSoOnline']->getMaHoSo()) {
                $bmTDL = $this->getModel()->layDanhSachDuLieuTDL(null, $lbm, null, $data['queryData']['sid'])->getDefaultResult();
            } else {
                $bmTDL = $this->getModel()->layDanhSachDuLieuTDL($data['queryData']['sid'], $lbm, null, null)->getDefaultResult();
            }
            $cn = [];
            $val = [];
            $val_total = [];
            foreach ($bmTDL as $key => $value) {
                array_push($cn,$value['CONTROL_NAME']);
                array_push($val,$value['GIA_TRI']);
                $val_total[$value['CONTROL_NAME']] = [
                    'GIA_TRI' => $value["GIA_TRI"],
                    'CHECKED' => $value["CHECKED"],
                    'TYPE' => $value["TYPE"]
                ];
            }
            $data['controlName'] = $cn;
            $data['giaTri'] = $val;
            $data['giaTriBML'] = $val_total;
        }
        else
        {
            $data['controlName'] = null;
            $data['giaTri'] = null;
            $data['giaTriBML'] = null;
        }

        $MA_THU_TUC = $data['thuTuc']->getMaThuTuc();

        // check có phải thủ tục Khai báo tạm trú và tài khoản công dân BCA đã đăng nhập hay chưa
        //  => redirect trang đăng nhập riêng
        $this->checkThuTucKBTT($MA_THU_TUC, $this->getRequest()->getRequestUri(), true);

        $tsqt_nop_hs_2b = Model\Entity\System\Parameter::fromId('BCA_CAU_HINH_TT_QUY_TRINH_NOP_HS_HAI_BUOC')->getValue();
        $tsqt_nop_hs_3b = Model\Entity\System\Parameter::fromId('BCA_CAU_HINH_TT_QUY_TRINH_NOP_HS_BA_BUOC')->getValue();
        $qt_nop_hs_2b = $tsqt_nop_hs_2b ? explode(',', $tsqt_nop_hs_2b) : [];
        $qt_nop_hs_3b = $tsqt_nop_hs_3b ? explode(',', $tsqt_nop_hs_3b) : [];

        if(in_array($MA_THU_TUC, $qt_nop_hs_2b)) {
            $this->getView()->render('chi-tiet-ho-so', $data);
        } else if(in_array($MA_THU_TUC, $qt_nop_hs_3b)) {
            $this->getView()->render('chi-tiet-ho-so-3b', $data);
        } else {
            header(sprintf('Location: %sbo-cong-an/home', SITE_ROOT));
            exit;
        }
    }

    public function thanhToanTrucTuyen() {
        $data['model'] = $this->getModel();
        $data['queryData'] = $data['model']->getQueryData();
        if (empty($data['queryData']['maHoSo'])) {
            exit('Mã hồ sơ không tồn tại!');
        }
        $data['model']->checkViewRequirement($data['queryData']['sid']);
        $data['hoSo'] = Entity\HoSo::fromMaHoSo((int) $data['queryData']['maHoSo']);
        if (empty($data['hoSo']->getMaHoSo())) {
            exit('Mã hồ sơ không chính xác!');
        }
        $data['hoSo']->laore();
        $data['dmLePhiHoSo'] = $data['hoSo']->getDmLePhiHoSo();
        $data['qttt'] = $data['hoSo']->getQttt();
        $data['thuTuc'] = $data['hoSo']->getThuTuc();
        $data['donViTiepNhan'] = $data['hoSo']->getDonViTiepNhan();
        $data['mucDo'] = $data['thuTuc']->getMucDo();
        $data['dmGiayToCuaHoSo'] = $data['hoSo']->getDanhSachGiayToNop();
        $data['congDan'] = $data['hoSo']->getCongDan();
        $data['hoSoEntity'] = $data['hoSo'];
        $this->getView()->render('thanh-toan-truc-tuyen', $data);
    }

    public function nopLePhiTrucTuyen() {
        $this->getModel()->nopLePhiTrucTuyen();
    }

    public function xacMinhHoSo() {
        $data['model'] = $this->getModel();
        $data['queryData'] = $data['model']->getQueryData();
        $hoSoOnline = Entity\HoSoOnline::fromMaHoSo((int) $data['queryData']['sid']);

        $thanhToanTrucTuyenMucDo23 = Model\Entity\System\Parameter::fromId('THANH_TOAN_TRUC_TUYEN_MUC_DO_2_3')->getValue();
        if (!$hoSoOnline->exists() && $thanhToanTrucTuyenMucDo23 == 1) {
            $hoSoOnline = Entity\HoSo::fromMaHoSo((int) $data['queryData']['sid']);
        }

        if ($hoSoOnline->getMaHoSo()) {
            $bootstrapURL = SITE_ROOT . 'bo-cong-an/tiep-nhan-online/nap-du-lieu-cap-nhat?' . http_build_query([
                'sid' => $data['queryData']['sid'],
                'return-url' => $data['queryData']['returnUrl']
            ]);
            if ($maCongDan = (int) $hoSoOnline->getMaCongDan()) {
                if ($maCongDan === (int) Entity\CongDan::fromSession()->getMaCongDan()) {
                    header(sprintf('Location: %s', $bootstrapURL));
                    exit;
                }
                header(sprintf('Location: %s', Entity\CongDan::getLoginLinkDVC($bootstrapURL)));
                exit;
            }
            header(sprintf('Location: %sbo-cong-an/tiep-nhan-online/xac-nhan-khoa-cap-nhat?%s', SITE_ROOT, http_build_query([
                'sid' => $data['queryData']['sid'],
                'return-url' => $bootstrapURL
            ])));
            exit;
        }
        $this->getView()->render('loi-ho-so-khong-ton-tai');
    }

    public function napDuLieuCapNhat() {
        $this->getModel()->napDuLieuCapNhat();
    }

    public function xacNhanKhoaCapNhat() {
        $resultInfo = new ResultInfo(null, null, []);
        if ($this->getRequest()->isPost()) {
            $resultInfo = $this->getModel()->kiemTraKhoaCapNhatHoSo();
        }
        $this->getView()->render('xac-nhan-khoa-cap-nhat', [
            'model' => $this->getModel(),
            'resultInfo' => $resultInfo
        ]);
    }

    public function luuThongTinHoSo() {
        $this->getModel()->luuThongTinHoSo();
    }

    public function nhapLePhiHoSo() {
        $data['model'] = $this->getModel();
        $data['queryData'] = $data['model']->getQueryData();
        $data['model']->checkUpdateRequirement($data['queryData']['sid']);
        $data['applier'] = new Applier($data['queryData']['sid']);
        $data['hoSoOnline'] = $data['applier']->sessionGet(Applier::ET_HO_SO_ONLINE);
        $data['dmGiayToCuaHoSo'] = $data['hoSoOnline']->getDanhSachGiayToNop();
        $data['qttt'] = $data['hoSoOnline']->getQttt();
        $data['thuTuc'] = $data['hoSoOnline']->getThuTuc();
        $MA_THU_TUC = $data['thuTuc']->getMaThuTuc();
        $tsqt_nop_hs_2b = Model\Entity\System\Parameter::fromId('BCA_CAU_HINH_TT_QUY_TRINH_NOP_HS_HAI_BUOC')->getValue();
        $tsqt_nop_hs_3b = Model\Entity\System\Parameter::fromId('BCA_CAU_HINH_TT_QUY_TRINH_NOP_HS_BA_BUOC')->getValue();
        $qt_nop_hs_2b = $tsqt_nop_hs_2b ? explode(',', $tsqt_nop_hs_2b) : [];
        $qt_nop_hs_3b = $tsqt_nop_hs_3b ? explode(',', $tsqt_nop_hs_3b) : [];
        if(in_array($MA_THU_TUC, $qt_nop_hs_2b) || in_array($MA_THU_TUC, $qt_nop_hs_3b)){
            $data['progress'] = new Progress($data['applier'], 4, 4);
        }else{
            $data['progress'] = new Progress($data['applier'], 4, 7);
        }
        $data['donViTiepNhan'] = $data['hoSoOnline']->getDonViTiepNhan();
        $data['mucDo'] = $data['thuTuc']->getMucDo();
        $data['dmHinhThucNopHoSo'] = new DanhMuc\HinhThucNopHoSo([
            'hoTroThuGomHoSo' => $data['thuTuc']->hoTroThuGomHoSo() && $data['dmGiayToCuaHoSo']->coGiayToPhaiNopGiay(),
            'loaiTiepNhan'    => [1,2]
        ]);
        $data['dmHinhThucNhanKetQua'] = new DanhMuc\HinhThucNhanKetQua([
            'hoTroPhatTraHoSo' => $data['thuTuc']->hoTroPhatTraHoSo(),
            'maThuTuc'         => $data['thuTuc']->getMaThuTuc(),
            'noiHienThi'       => 1
        ]);
        $data['dmLePhiThuTuc'] = new DanhMuc\LePhiThuTuc([
            'maThuTuc' => $data['thuTuc']->getMaThuTuc(),
            'trangThai' => 1
        ]);
        $data['dmLePhiHoSo'] = $data['hoSoOnline']->getDmLePhiHoSo();
        $data['dmLoaiLePhiCuaThuTuc'] = new DanhMuc\LoaiLePhi([
            'provider'              => DanhMuc\LoaiLePhi::CUA_THU_TUC,
            'maThuTuc'              => $data['thuTuc']->getMaThuTuc(),
            'lePhiThuTuc_trangThai' => 1,
            'tt_online_lp'          => [1,2],
            'maQttt'                => $data['qttt']->getMaQttt()
        ]);
        $data['congDan'] = $data['hoSoOnline']->getCongDan();
        $data['caThiSoLuongDK'] = 0;
        if($dLCaThi = $data['applier']->sessionGet('DL_CA_THI')){
            $data['caThiSoLuongDK'] = $dLCaThi['P_SO_LUONG_DANG_KY'];
        }
        $data['bca_phi_tt'] = $data['applier']->sessionGet('BCA_PHI_TT');
        // hienctt BCA lấy lệ phí từ form thông tin người nộp
        if(empty($data['bca_phi_tt'])) {
            $lbm = $data['hoSoOnline']->layMaEformID();
            if($lbm)
            {
                $bmTDL = $this->getModel()->layDanhSachDuLieuTDL($data['queryData']['sid'], $lbm, null,null)->getDefaultResult();
                foreach ($bmTDL as $key => $value) {
                    if($value['CONTROL_NAME'] == '_fsLePhiNopHoSo') {
                        $data['bca_phi_tt'] = $value['GIA_TRI'];
                        break;
                    }
                }
            }
        }
        $this->getView()->render('le-phi-ho-so-nop', $data);
    }

    public function danhGiaDichVuCong () {
        $data['model'] = $this->getModel();
        $data['queryData'] = $data['model']->getQueryData();
        $data['model']->checkViewRequirement($data['queryData']['sid']);

        $mucDanhGia = $this->model->SELECT_DM_DANH_GIA_DVTT();
        $arr_muc_danh_gia = [];
        foreach ($mucDanhGia as $value) {
            if ($value['TRANG_THAI'] == 0) {
                array_push($arr_muc_danh_gia, [
                    'MA_MUC'  => $value['MA_MUC'],
                    'TEN_MUC' => $value['TEN_MUC']
                ]);
            }
        }

        $this->getView()->render('danh-gia-dich-vu-cong', [
            'arr_muc_danh_gia' => $arr_muc_danh_gia,
            'maHoSo'           => $data['queryData']['maHoSo']
        ]);
    }

    public function update_y_kien () {
        $this->model->goback_url = $this->view->get_controller_url() ;
        $this->model->INSERT_HS_DANH_GIA_DVTT();
    }

    public function exportTemplate() {
        $this->getModel()->exportTemplate();
    }

    public function dsp_all_coquan () {
        $this->getView()->render('dsp-all-co-quan', [
            'model' => $this->getModel()
        ]);
    }

    public function layThongTinNguoiNopTuSession() {
        $this->getModel()->layThongTinNguoiNopTuSession();
    }
    public function index2() {
        $this->view->render('index2');
    }
    public function test() {
        $this->view->render('index-nop-ho-so-qua-mang');
    }
    public function in_phieutiepnhan() {
        $this->block->call('ptn');
        $this->block->ptn->exec();
    }

    public function ajaxSelectCoQuanNopHoSo() {
        $model = $this->getModel();
        $options = $model->getRequestData();
        echo $model->layDanhMucCoQuanNopHoSo($options)->toHtmlOptions(function ($item) {
            return $item->getMaCoQuan();
        }, function($item) {
            return $item->getTenCoQuan();
        });
    }

    public function ajaxSelectLinhVucNopHoSo() {
        $model = $this->getModel();
        $options = $model->getRequestData();
        echo $model->layDanhMucLinhVucNopHoSo($options)->toHtmlOptions(function ($item) {
            return $item->getMaLinhVuc();
        }, function($item) {
            return $item->getTenLinhVuc();
        });
    }

    public function updateHuyCaThi($soHoSo) {
        return (new \Oracle\OracleFunction\UPDATE_HUY_CA_THI(['P_SO_HO_SO' => $soHoSo]))->getResult();
    }

    public function ajax_thong_tin_khach_hang() {
        $user = Entity\System\Parameter::fromId('LTHS_USER')->getValue();
        $pass = Entity\System\Parameter::fromId('LTHS_PASS')->getValue();
        $maKhachHang = get_post_var('maKhachHang');
        $data = [
            "Account" => ["User" => $user, "Pass" => $pass],
            'MaKH' => $maKhachHang
        ];
        $data_string = json_encode($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://wsigate.softdaklak.vn/api/ThongTinHoSo/GetThongTinKH');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json', 'Content-Length: ' . strlen($data_string)]);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6');
        $rs = curl_exec($ch);
        curl_close($ch);
        echo $rs;
    }

    public function ajax_get_ma_vilis(){
        $maQuanHuyen = get_post_var('maQuanHuyen');
        $maQttt = get_post_var('maQttt');
        echo $this->model->getMaVilis($maQttt,$maQuanHuyen);

    }

    public function ajaxFormGiayToVilis($sid) {
        $soLuongFileDinhKemBatBuoc = (int) Entity\System\Parameter::fromId('vilis_SoLuongFileDinhKemBatBuoc_nopHoSoOnline')->getValue();
        $this->model->call('vilis');
        $this->block->call('hoso')->view_giaytodinhkemvilis(get_post_var('maLoaiGiaoDich'),$soLuongFileDinhKemBatBuoc, $sid, 'bo-cong-an/tiep-nhan-online/nhap-thong-tin-ho-so');
    }

    public function thongBaoLoi(){
        $model = $this->getModel();
        $queryData = $model->getQueryData();
        $applier = new Applier($queryData['sid']);
        $message = $applier->sessionGet(Applier::ER_MESSAGE);
        $this->getView()->render('thong-bao-loi', [
            'message' => $message
        ]);
    }

    public function ajaxSelectCapThuTucNopHoSo() {
        $model = $this->getModel();
        $options = $model->getPostData();
        echo $model->layDanhMucCapThuTuc($options)->toHtmlOptions(function ($item) {
            return $item->getMaCap();
        }, function($item) {
            return $item->getTenCap();
        }, null, 0);
    }
    // Nhi 29/05/2019 - IGATESUPP-10601 - Yêu cầu người dân bổ sung online đối với trường hợp tạm dừng chờ bổ sung
    public function nhapThongTinHoSoOnline() {
        $data['model'] = $this->getModel();
        $data['queryData'] = $data['model']->getQueryData();
        $data['model']->checkUpdateRequirement($data['queryData']['sid']);
        $data['applier'] = new Applier($data['queryData']['sid']);
        $data['progress'] = new Progress($data['applier'], 3, 4);
        $data['hoSoOnline'] = $data['applier']->sessionGet(Applier::ET_HO_SO_ONLINE);
        $data['qttt'] = $data['hoSoOnline']->getQttt();
        $data['thuTuc'] = $data['hoSoOnline']->getThuTuc();
        $data['congDan'] = $data['hoSoOnline']->getCongDan();
        $data['dmGiayToCuaHoSo'] = $data['hoSoOnline']->getDanhSachGiayToNop();
        $data['donViTiepNhan'] = $data['hoSoOnline']->getDonViTiepNhan();
        $data['mucDo'] = $data['thuTuc']->getMucDo();
        $data['congViecTiepNhan'] = $data['qttt']->layCongViecTiepNhan();
        $data['hoSoVilis'] = Entity\HoSoVilis::fromProperties(['maVilis' => null]);
        $data['dmGiayToCuaThuTuc'] = new DanhMuc\GiayToCuaThuTuc([
            'maThuTuc' => $data['thuTuc']->getMaThuTuc(),
            'maCongViecQttt' => $data['congViecTiepNhan'] ? $data['congViecTiepNhan']->getMaCongViecQttt() : null,
            'loaiGiayToThaoTac' => 0,
            'trangThai' => 0
        ]);
        $data['lienThongHoSoHTK'] = 0;
        $data['sid'] = $data['queryData']['sid'];
        $vilisFlag =  Entity\System\Parameter::fromId('LIEN_THONG_VILIS')->getValue();
        $maQuanHuyenTn = $data['hoSoOnline']->getDonViTiepNhan()->getMaQuanHuyen();
        $maViLis = $data['model']->getMaVilis($data['qttt']->getMaQttt(),$maQuanHuyenTn);
        $data['lienThongVilis'] = (int)($vilisFlag && $maViLis);
        $data['applier']->sessionSet(Applier::VILIS_ACTIVE, $data['lienThongVilis']);
        if ($lths = Entity\System\Parameter::fromId('DV_LIEN_THONG_HO_SO')->getValue()) {
            $ttlt = Entity\ThuTucLienThong::fromMaThuTuc($data['thuTuc']->getMaThuTuc());
            if ($lths == 'DAKLAK' && $ttlt->getLinkWs() != '') {
                $data['lienThongHoSoHTK'] = 'DAKLAK';
            }
        }
        $data['choPhepHienThiTPHSKhac'] = (new Oracle\StoreProcedure\SELECT_TT_CAU_HINH_QUY_TRINH([
                                           'P_MA_THU_TUC' => $data['thuTuc']->getMaThuTuc(),
                                           'P_MA_QTTT' => $data['qttt']->getMaQttt()]))->getDefaultResult();
        $data['dmGiayToBS'] = (new Oracle\StoreProcedure\SELECT_HS_CO_GIAY_TO_BS([
                                'P_MA_HO_SO_ONLINE' => $data['queryData']['sid'],
                                'P_MA_CO_GIAY_TO_KHAC' => null,
                                'P_MA_HO_SO' => null
                              ]))->getDefaultResult();
        $this->getView()->render('thong-tin-ho-so-online', $data);
    }

    public function SaveGiayToBoSung(){
        return $this->model->SaveGiayToBoSung();
    }

    // Nhi IGATESUPP-17081 17/10/2019
    public function checkAnHienLePhi(){
        if(Entity\System\Parameter::fromId('AN_KHI_LE_PHI_0_NOP_HS_ONLINE')->getValue() == 1 || get_post_var('count') == 0)
            echo 1;
        else
            echo 0;
    }

    // Giao diện đăng nhập khai báo tạm trú
    public function loginKBTT() {
        $request = $this->getRequest();
        $filter = $this->getFilter();
        $P_MA_THU_TUC = $filter->filter($request->getQuery('matt'));
        if(!$P_MA_THU_TUC) {
            header(sprintf('Location: %sbo-cong-an/home', SITE_ROOT));
            exit;
        }
        $check_login = Model\Entity\System\Parameter::fromId('BCA_THU_TUC_YC_LOGIN_REDIRECT_BCA')->getValue();
        $require_tt = explode(',', $check_login);
        if(!in_array($P_MA_THU_TUC, $require_tt)) {
            header(sprintf('Location: %sbo-cong-an/home', SITE_ROOT));
            exit;
        }

        $P_MA_CO_QUAN = (New Package\BCA_DBLT())->GET_MA_CQ_THEO_TT([
            'P_MA_THU_TUC' => $P_MA_THU_TUC
        ]);
        if(!$P_MA_CO_QUAN || empty($P_MA_CO_QUAN['MA_CO_QUAN'])) {
            header(sprintf('Location: %sbo-cong-an/home', SITE_ROOT));
            exit;
        }
        $option = '<option value="">-- Chưa chọn --</option>';
        $dm = (New Package\BCA_DBLT())->GET_DS_DC_TRUC_TIEP_BANG_MAP([
            'P_CAP_DIA_CHINH' => 'T',
            'P_MA_CO_QUAN' => $P_MA_CO_QUAN['MA_CO_QUAN'],
            'P_MA_LOAI_LIEN_THONG' => null,
            'P_MA_THU_TUC' => $P_MA_THU_TUC,
            'P_MA_DIA_CHINH' => null
        ])->getDefaultResult();
        foreach ($dm as $detail) {
            $option .= '<option value="'.$detail['MA_CUA_BCA'].'">'.$detail['TEN'].'</option>';
        }
        $viewData['option_dmTT'] = $option;
        $viewData['MA_THU_TUC'] = $P_MA_THU_TUC;
        $viewData['MA_CO_QUAN'] = $P_MA_CO_QUAN['MA_CO_QUAN'];
        $viewData['RETURN_URL'] = $filter->filter($request->getQuery('return-url'));

        $this->view->render('dang-nhap-tk-khai-bao-tam-tru', $viewData);
    }

    /**
     * Trạng thái lỗi:
     * 0 : thành công
     * 1 : lỗi thiếu dữ liệu đẩy lên
     * 2 : lỗi xác thực không thành công
     */
    public function checkLoginTKBCA() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $filter = $this->getFilter();
            $username = trim($filter->filter($request->getPost('username')));
            $password = trim($filter->filter($request->getPost('password')));
            $macoquan = trim($filter->filter($request->getPost('macoquan')));
            $inputCSLT = trim($filter->filter($request->getPost('inputCSLT')));
            $return_url = trim($filter->filter($request->getPost('return_url')));
            $return_url = !empty($return_url) ? $return_url : sprintf('%sbo-cong-an/home', SITE_ROOT);

            if(empty($username) || empty($password) || empty($macoquan) || empty($inputCSLT)) {
                echo json_encode([
                    'code' => 1,
                    'message' => 'Tài khoản không hợp lệ.'
                ]);
                exit();
            }
            $user = (new Oracle\Package\BCA_LIEN_THONG())->XAC_THUC_TK_BCA([
                'P_USERNAME' => $username,
                'P_PASSWORD' => md5($password),
                'P_MA_CO_QUAN' => $macoquan,
                'P_MA_TINH_THANH' => $inputCSLT
            ]);

            if($user->count() == 1) {
                $user_arr = [];
                foreach ($user as $u) {
                    $user_arr['ID'] = $u->ID;
                    $user_arr["USERNAME"] = $u->USERNAME;
                    $user_arr["PASSWORD"] = $u->PASSWORD;
                    $user_arr["TRANG_THAI"] = $u->TRANG_THAI;
                    $user_arr["MA_CONG_DAN_IGATE"] = $u->MA_CONG_DAN_IGATE;
                    $user_arr["MA_TINH_THANH"] = $u->MA_TINH_THANH;
                    $user_arr["MA_QUAN_HUYEN"] = $u->MA_QUAN_HUYEN;
                    $user_arr["MA_PHUONG_XA"] = $u->MA_PHUONG_XA;
                    $user_arr["DIA_CHI_CHI_TIET"] = $u->DIA_CHI_CHI_TIET;
                    $user_arr["MA_CO_QUAN"] = $u->MA_CO_QUAN;
                    $user_arr["NGAY_CAP_NHAT"] = $u->NGAY_CAP_NHAT;
                    $user_arr["TEN_CSLT"] = $u->TEN_CSLT;
                    break;
                }
                Session::set(TIEP_DAU_NGU_SESSION.'BCA_REDIRECT_CHECK_LOGINED', $user_arr);
                if (preg_match('/^https?/i', $return_url)) {
                    $return_url = sprintf('%sbo-cong-an/home', SITE_ROOT);
                }
                echo json_encode([
                    'code' => 0,
                    'data' => $return_url,
                    'message' => 'Đăng nhập thành công'
                ]);
                exit();
            }
            echo json_encode([
                'code' => 2,
                'message' => 'Tài khoản chưa đúng hoặc chưa được đồng bộ.'
            ]);
            exit();
        }
        echo json_encode([
            'code' => 2,
            'message' => 'Xác thực không thành công.'
        ]);
        exit();
    }

    // Check nếu mã thủ tục không được cấu hình 2 hoặc 3 bước
    public function checkQuyTrinhBCA($maThuTucPublic = '') {
        // // Check nếu không phải số hồ sơ được cấu hình theo url BCA
        // $tsqt_nop_hs_2b = Model\Entity\System\Parameter::fromId('BCA_CAU_HINH_TT_QUY_TRINH_NOP_HS_HAI_BUOC')->getValue();
        // $tsqt_nop_hs_3b = Model\Entity\System\Parameter::fromId('BCA_CAU_HINH_TT_QUY_TRINH_NOP_HS_BA_BUOC')->getValue();
        // $qt_nop_hs_2b = $tsqt_nop_hs_2b ? explode(',', $tsqt_nop_hs_2b) : [];
        // $qt_nop_hs_3b = $tsqt_nop_hs_3b ? explode(',', $tsqt_nop_hs_3b) : [];
        // if(!in_array($maThuTucPublic, $qt_nop_hs_2b) && !in_array($maThuTucPublic, $qt_nop_hs_3b)) {
        //     header(sprintf('Location: %sbo-cong-an/home', SITE_ROOT));
        //     exit;
        // }
    }

    // yêu cầu tài khoản login phải đã sso lên cổng quốc gia
    public function checkSSOTKQUOCGIA($maThuTucPublic = '') {
        $yc_sso_qg = Model\Entity\System\Parameter::fromId('BCA_THU_TUC_YC_SSO_TK_QUOC_GIA')->getValue();
        $ex_yc_sso_qg = explode(',', $yc_sso_qg);
        if(in_array($maThuTucPublic, $ex_yc_sso_qg)) {
            Entity\CongDan::forceLoginIfDoesNotDVC($this->getRequest()->getRequestUri());
            $ss_congdan = Session::get(TIEP_DAU_NGU_SESSION . 'DU_LIEU_CONG_DAN');
            if($ss_congdan->P_MA_CONG_DAN) {
                $congdan_logined = (new Package\BCA_DICH_VU_CONG())->GET_TECHID_SSO_QUOC_GIA([
                    'P_MA_CONG_DAN' => $ss_congdan->P_MA_CONG_DAN
                ]);
            } else {
                Entity\CongDan::forceLoginIfDoesNotDVC($this->getRequest()->getRequestUri());
            }
            if(empty($congdan_logined->TECHID_VNCONNECT_SSO)) {
                header(sprintf('Location: %sdich-vu-cong/cong-dan?sso_cd=1', SITE_ROOT));
                exit;
            }
        }
    }

    // Check thủ tục khai báo tạm trú. check tài khoản công dân BCA đã đồng bộ lên iGate.
    public function checkThuTucKBTT ($MA_THU_TUC = '', $url = '', $redirect = false) {
        $thutuc_require_login = Model\Entity\System\Parameter::fromId('BCA_THU_TUC_YC_LOGIN_REDIRECT_BCA')->getValue();
        $require_tt = explode(',', $thutuc_require_login);
        if(in_array($MA_THU_TUC, $require_tt)) {
            if($redirect == false) {
                //=> Quay lại màn hình chi tiết thủ tục
                Entity\CongDan::forceLoginTKCongDanBCADongBo($MA_THU_TUC);
            } else {
                //=> redirect trang đăng nhập riêng
                Entity\CongDan::forceLoginIfDoesNotKBTTBCA($url, $MA_THU_TUC);
            }
        }
    }

    public function checkCanUpdateORDeleteCCCD($sohoso, $sid) {
        if($sohoso) { // đã có số hồ sơ
            $anNutUpdateTheoLV =  Entity\System\Parameter::fromId("AN_NUT_CAP_NHAN_HS_THEO_LV")->getValue();
            $arrAnNutUpdate = !empty($anNutUpdateTheoLV) ? explode("$",$anNutUpdateTheoLV) : [];
            $CHECK_DANH_DAU_HS = (New Package\BCA_LIEN_THONG())->CHECK_DANH_DAU_HS([
                'P_SO_HO_SO' => $sohoso
            ]);
            $malinhvucTT = $CHECK_DANH_DAU_HS->MA_LINH_VUC_THU_TUC;
            if(!in_array($malinhvucTT, $arrAnNutUpdate)) {
                // được tiếp tục sửa
                return true;
            } else {
                header(sprintf('Location: %sbo-cong-an/tiep-nhan-online/thong-bao-loi?sid=%s', SITE_ROOT, $sid));
                exit;
            }
        }
        return true;
    }

}
