<?php
if (!defined('SERVER_ROOT')) {
    exit('No direct script access allowed');
}
use Oracle\Package;
use Oracle\OracleFunction;
use Oracle\Connection;
use Model\Entity;
use Model\DanhMuc;
use Model\Auth;
use Model\VNPost;
use Model\DichVuCong\Applier;
use Nth\ResultInfo;
use Nth\Bootstrap\Alert\Alert;
use Zend\Validator\Csrf;
use Model\System;
use Model\Entity\LogData;
use Nth\Bootstrap\Pagination\Pagination;
use Nth\Helper\Convertor;
use Model\VNPT\SMS;
use Nth\FormBuilder;
use Nth\FormBuilder\DataType;

class tiepnhanonline_Model extends Model {
    private $postData;
    public function getQueryData() {
        $request = $this->getRequest();
        $filter = $this->getFilter();
        return [
            'sid' => $filter->filter($request->getQuery('sid')),
            'token' => $filter->filter($request->getQuery('token')),
            'page' => $filter->filter($request->getQuery('page', 1)),
            'maHoSo' => $filter->filter($request->getQuery('ma-ho-so')),
            'maThuTuc' => $filter->filter($request->getQuery('ma-thu-tuc')),
            'maThuTucPublic' => $filter->filter($request->getQuery('ma-thu-tuc-public')),
            'tenThuTuc' => $filter->filter($request->getQuery('ten-thu-tuc')),
            'maMucDo' => $filter->filter($request->getQuery('ma-muc-do')),
            'maCoQuan' => MA_CO_QUAN ?: $filter->filter($request->getQuery('ma-co-quan')),
            'maDonVi' => !MA_CO_QUAN ? (MA_DON_VI ?: $filter->filter($request->getQuery('ma-don-vi'))) : '',
            'maLinhVuc' => $filter->filter($request->getQuery('ma-linh-vuc')),
            'maQttt' => $filter->filter($request->getQuery('ma-qttt')),
            'maQuanHuyenNop' => $filter->filter($request->getQuery('ma-quan-huyen-nop')),
            'maPhuongXaNop' => $filter->filter($request->getQuery('ma-phuong-xa-nop')),
            'maCapThuTuc' => $filter->filter($request->getQuery('ma-cap-thu-tuc')),
            'returnUrl' => $request->getQuery('return-url'),
            'templateId' => $request->getQuery('template-id'),
            'responseVietinbank' => $request->getQuery('responseVietinbank'),
            'responseCode' => $request->getQuery('responseCode')
        ];
    }

    public function getPostData() {
        $request = $this->getRequest();
        $filter = $this->getFilter();
        if(!$this->postData){
            $this->postData = [
            'sid' => $filter->filter($request->getPost('sid')),
            'page' => $filter->filter($request->getPost('page', 1)),
            'maHoSo' => $filter->filter($request->getPost('maHoSo')),
            'maThuTuc' => $filter->filter($request->getPost('maThuTuc')),
            'maThuTucPublic' => $filter->filter($request->getPost('maThuTucPublic')),
            'tenThuTuc' => $filter->filter($request->getPost('tenThuTuc')),
            'maMucDo' => $filter->filter($request->getPost('maMucDo')),
            'maCoQuan' => MA_CO_QUAN ?: $filter->filter($request->getPost('maCoQuan')),
            'maDonVi' => !MA_DON_VI ? (MA_DON_VI ?: $filter->filter($request->getPost('maDonVi'))) : '',
            'maLinhVuc' => $filter->filter($request->getPost('maLinhVuc')),
            'maQttt' => $filter->filter($request->getPost('maQttt')),
            'maQuanHuyenNop' => $filter->filter($request->getPost('maQuanHuyenNop')),
            'maQuanHuyenMapQtVilis' => $filter->filter($request->getPost('maQuanHuyenMapQtVilis')),
            'maPhuongXaNop' => $filter->filter($request->getPost('maPhuongXaNop')),
            'code' => $filter->filter($request->getPost('code')),
            'returnUrl' => $request->getPost('returnUrl'),
            'currentUrl' => $request->getPost('currentUrl'),
            'maPhuongXaNguoiGui' => $request->getPost('maPhuongXaNguoiGui'),
            'maPhuongXaNguoiNhan' => $request->getPost('maPhuongXaNguoiNhan'),
            'phiThuHo' => $filter->filter($request->getPost('phiThuHo')),
            'khoaCapNhat' => $filter->filter($request->getPost('khoaCapNhat')),
            'templateId' => $filter->filter($request->getPost('templateId')),
            'dsGiayToKhac' => json_decode($request->getPost('dsGiayToKhac'),true),
            'dsGiayToBoSung' => json_decode($request->getPost('dsGiayToBoSung'),true),
            'dsGiayToBoSungOld' => json_decode($request->getPost('dsGiayToBoSungOld'),true),
            'soHoSo' => $filter->filter($request->getPost('soHoSo')),
            'fileDaThanhToan' => $filter->filter($request->getPost('fileDaThanhToan')),
            'bcangayhen' => $filter->filter($request->getPost('txt_NGAY_HEN')),
            'bcabuoihen' => $filter->filter($request->getPost('radio-buoi-hen')),
            ];
        }
        return $this->postData;
    }

    public function getRequestData()
    {
        if(!$this->postData){
                $this->postData = [
                'sid' => get_request_var('sid'),
                'page' => get_request_var('page', 1),
                'maHoSo' => get_request_var('maHoSo'),
                'maThuTuc' => get_request_var('maThuTuc'),
                'maThuTucPublic' => get_request_var('maThuTucPublic'),
                'tenThuTuc' => get_request_var('tenThuTuc'),
                'maMucDo' => get_request_var('maMucDo'),
                'maCoQuan' => MA_CO_QUAN ?: get_request_var('maCoQuan'),
                'maDonVi' => !MA_DON_VI ? (MA_DON_VI ?: get_request_var('maDonVi')) : '',
                'maLinhVuc' => get_request_var('maLinhVuc'),
                'maQttt' => get_request_var('maQttt'),
                'maQuanHuyenNop' => get_request_var('maQuanHuyenNop'),
                'maPhuongXaNop' => get_request_var('maPhuongXaNop'),
                'code' => get_request_var('code'),
                'returnUrl' => get_request_var('returnUrl'),
                'currentUrl' => get_request_var('currentUrl'),
                'maPhuongXaNguoiGui' => get_request_var('maPhuongXaNguoiGui'),
                'maPhuongXaNguoiNhan' => get_request_var('maPhuongXaNguoiNhan'),
                'phiThuHo' => get_request_var('phiThuHo'),
                'khoaCapNhat' => get_request_var('khoaCapNhat'),
                'templateId' => get_request_var('templateId'),
                'dsGiayToKhac' => json_decode(get_request_var('dsGiayToKhac'),true)
            ];
        }
        return $this->postData;
    }

    public function layDanhMucCoQuanByThuTucPublic(array $options = []) {
        return new DanhMuc\CoQuan(array_merge($options, [
            'provider' => DanhMuc\CoQuan::THU_TUC_DAI_DIEN
        ]));
    }

    public function layDanhMucQtttNopHoSo(array $options = []) {
        return new DanhMuc\Qttt(array_merge($options, [
            'provider' => DanhMuc\Qttt::THU_TUC_DAI_DIEN
        ]));
    }

    public function layDanhMucQuanHuyenNopHoSo(array $options = []) {
        return new DanhMuc\QuanHuyen(array_merge($options, [
            'provider' => DanhMuc\QuanHuyen::DICH_VU_CONG_TRUC_TUYEN
        ]));
    }

    public function layDanhMucQuanHuyenCoMapQtVilis(array $options = []) {
        return new DanhMuc\QuanHuyen(array_merge($options, [
            'provider' => DanhMuc\QuanHuyen::CO_MAP_QT_VILIS
        ]));
    }

    public function layDanhMucPhuongXaNopHoSo(array $options = []) {
        return new DanhMuc\PhuongXa(array_merge($options, [
            'provider' => DanhMuc\PhuongXa::DICH_VU_CONG_TRUC_TUYEN
        ]));
    }

    public function kiemTraTruongHopHoSo() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            return $this->luuTruongHopHoSo($this->getPostData());
        }
        $queryData = $this->getQueryData();
        if ($url = Entity\ThuTuc::fromMaThuTuc($queryData['maThuTucPublic'])->getLinkNopTrucTuyen()) {
            header(sprintf('Location: %s', $url));
            exit;
        }
        $dmCoQuan = $this->layDanhMucCoQuanByThuTucPublic([
            'maThuTucPublic' => $queryData['maThuTucPublic'],
            'maCoQuan' => $queryData['maCoQuan']
        ]);
        if ($dmCoQuanCount = $dmCoQuan->count()) {
            if ($dmCoQuanCount > 1) {
                return true;
            }
            $dmQttt = $this->layDanhMucQtttNopHoSo([
                'maThuTucPublic' => $queryData['maThuTucPublic'],
                'maCoQuan' => $dmCoQuan->getItemByIndex(0)->getMaCoQuan()
            ]);
            if ($dmQtttCount = $dmQttt->count()) {
                // hienctt - Thêm function fix riêng qttt của lưu trú.
                $getMaQTTT = $this->getQTTTCoSoLuuTruC06($queryData['maThuTucPublic']);
                if ($dmQtttCount > 1 && $getMaQTTT == '') {
                    return true;
                }
                $data['maQttt'] = $getMaQTTT ? $getMaQTTT : $dmQttt->getItemByIndex(0)->getMaQttt();
                $data['maPhuongXaNop'] = $data['maQuanHuyenNop'] = null;
                $dmQuanHuyen = $this->layDanhMucQuanHuyenNopHoSo([
                    'maQttt' => $data['maQttt']
                ]);
                if ($dmQuanHuyenCount = $dmQuanHuyen->count()) {
                    if ($dmQuanHuyenCount > 1) {
                        return true;
                    }
                    $data['maQuanHuyenNop'] = $dmQuanHuyen->getItemByIndex(0)->getMaQuanHuyen();
                    $dmPhuongXa = $this->layDanhMucPhuongXaNopHoSo([
                        'maQttt' => $data['maQttt'],
                        'maQuanHuyen' => $data['maQuanHuyenNop'],
                        'maPhuongXaNop' => $queryData['maPhuongXaNop']
                    ]);
                    if ($dmPhuongXaCount = $dmPhuongXa->count()) {
                        if ($dmPhuongXaCount > 1) {
                            return true;
                        }
                        $data['maPhuongXaNop'] = $dmPhuongXa->getItemByIndex(0)->getMaPhuongXa();
                    }
                }
                $dmQuanHuyenCoMapQtVilis = $this->layDanhMucQuanHuyenCoMapQtVilis([
                    'maQttt' => $data['maQttt']
                ]);
                if ($dmQuanHuyenCoMapQtVilisCount = $dmQuanHuyenCoMapQtVilis->count()) {
                    if ($dmQuanHuyenCoMapQtVilisCount > 1) {
                        return true;
                    }
                    $data['maQuanHuyenMapQtVilis'] = $dmQuanHuyenCoMapQtVilis->getItemByIndex(0)->getMaQuanHuyen();
                }
                return $this->luuTruongHopHoSo($data);
            }
        }
        exit('[ERROR-1] Không tìm thấy quy trình xử lý online');
    }

    // Set riêng mã qttt cho cơ sở lưu trú C06
    function getQTTTCoSoLuuTruC06($maThuTucPublic = '') {
        $QTTT_LUUTRU = getThamSoArray(Model\Entity\System\Parameter::fromId('BCA_QUYTRINH_THUTUC_LUUTRU', ['cache' => false])->getValue());
        $MATHUTUC_LT = !empty($QTTT_LUUTRU['mathutuc']) ? $QTTT_LUUTRU['mathutuc'] : '';
        $QTTT_LT_CONGDAN = !empty($QTTT_LUUTRU['congdan']) ? $QTTT_LUUTRU['congdan'] : '';
        $QTTT_LT_CSLT = !empty($QTTT_LUUTRU['cslt']) ? $QTTT_LUUTRU['cslt'] : '';

        if( $MATHUTUC_LT == $maThuTucPublic) {
            $congdan = new \Model\CongDan();
            $data = $congdan->getSessionData();
            if(!empty($data->P_TEN_DANG_NHAP)) {
                $rs = (new Package\BCA_DBLT())->CHECK_EXIST_MAPPING_TK_CSDL([
                    'P_TEN_DANG_NHAP' => $data->P_TEN_DANG_NHAP,
                    'P_MA_CO_QUAN' => 'C06',
                    'P_MA_PMLT' => 'PMCCCD'
                ]);
                if($rs->P_VAL == 'EXIST') {
                    // là tài khoản CSLT
                    return $QTTT_LT_CSLT;
                } else {
                    // Là tài khoản công dân
                    return $QTTT_LT_CONGDAN;
                }
            }
        }
        return '';
    }

    public function luuTruongHopHoSo(array $data) {
        $qttt = Entity\Qttt::fromMaQttt($data['maQttt'], ['lre_options' => ['ThuTuc']]);
        $thuTuc = Entity\ThuTuc::fromMaThuTuc($qttt->getMaThuTuc(), ['lre_options' => ['MucDo']]);
        $donViTiepNhan = $qttt->layDonViTiepNhan($data['maQuanHuyenNop'], $data['maPhuongXaNop']);
        $hoSoOnline = new Entity\HoSoOnline();
        $hoSoOnline->setMaThuTuc($thuTuc->getMaThuTuc());
        $hoSoOnline->setMaCoQuan($thuTuc->getMaCoQuan());
        $hoSoOnline->setMaQuanHuyenNop($data['maQuanHuyenNop']);
        $hoSoOnline->setMaPhuongXaNop($data['maPhuongXaNop']);
        $hoSoOnline->laore();
        $hoSoOnline->setQttt($qttt);
        $hoSoOnline->setMaQttt($qttt->getMaQttt());
        $hoSoOnline->setBieuMauNguoiNop($thuTuc->layBieuMauDangSuDung(Entity\BieuMau::BIEU_MAU_NGUOI_NOP_HO_SO, Entity\BieuMau::DISPLAY_ALL));
        $hoSoOnline->setMaDonViTiepNhan($donViTiepNhan->getMaDonVi());
        $hoSoOnline->setDonViTiepNhan($donViTiepNhan);
        $hoSoOnline->setCongDan(Entity\CongDan::fromSession());
        $hoSoOnline->setSoCongViecQttt($qttt->getSoCongViecQttt());
        $maQuanHuyenThuaDat = empty($data['maQuanHuyenMapQtVilis'])? $data['maQuanHuyenNop'] : $data['maQuanHuyenMapQtVilis'];
        $sid = Applier::generateId($data['maQttt']);
        (new Applier($sid))->sessionSet(Applier::ET_HO_SO_ONLINE, $hoSoOnline);
        (new Applier($sid))->sessionSet(Applier::VILIS_MA_QUAN_HUYEN_THUA_DAT, $maQuanHuyenThuaDat);
        header(sprintf('Location: %sbo-cong-an/tiep-nhan-online/nhap-thong-tin-nguoi-nop-ho-so?sid=%s', SITE_ROOT, $sid));
        exit;
    }

    public function luuThongTinNguoiNopHoSo() {
        $request = $this->getRequest();
        $filter = $this->getFilter();
        $data = $this->getPostData();
        //fix bug security
        $csrf = new Csrf();
        if($csrf->isValid($filter->filter($request->getPost('tokencsrf')))){
            $session = $csrf->getSession();
            $session->getManager()->getStorage()->clear($csrf->getSessionName());
        }else{
            exit('Permission denied');
        }
        $dsHSOnline = [
                'tenCongDan'
                , 'tenCoQuanToChuc'
                , 'soCmnd'
                , 'noiCapCmnd'
                , 'diDong'
                , 'fax'
                , 'email'
                , 'website'
                , 'maPhuongXa'
                , 'diaChi'
                , 'ngayCapCmnd'
                , 'phuongXa'
                , 'soGCNGP'
                , 'ngaySinh'
                , 'gioiTinh'
                , 'danToc'
                , 'maDMDiaChi'
                , 'maDMQuocGia'
                , 'diaChiNuocNgoai'
                , 'phuongXa_maQuanHuyen'
                , 'phuongXa_quanHuyen_maTinhThanh'
                , 'ngaySinhCongDan'
                , 'gioiTinhCongDan'
                , 'danTocCongDan'
                , 'maBieuMau'
                , 'duLieuBieuMau'
                , 'duLieuEform' // hienctt - gửi biểu mẫu dạng xml
            ];

        $applier = new Applier($data['sid']);
        $hoSoOnline = $applier->sessionGet(Applier::ET_HO_SO_ONLINE);
        $MA_THU_TUC = $hoSoOnline->getThuTuc()->getMaThuTuc();
        $tsqt_nop_hs_2b = Model\Entity\System\Parameter::fromId('BCA_CAU_HINH_TT_QUY_TRINH_NOP_HS_HAI_BUOC')->getValue();
        $tsqt_nop_hs_3b = Model\Entity\System\Parameter::fromId('BCA_CAU_HINH_TT_QUY_TRINH_NOP_HS_BA_BUOC')->getValue();
        $qt_nop_hs_2b = $tsqt_nop_hs_2b ? explode(',', $tsqt_nop_hs_2b) : [];
        $qt_nop_hs_3b = $tsqt_nop_hs_3b ? explode(',', $tsqt_nop_hs_3b) : [];

        if(in_array($MA_THU_TUC, $qt_nop_hs_2b) || in_array($MA_THU_TUC, $qt_nop_hs_3b)){
            $url = sprintf('%sbo-cong-an/tiep-nhan-online/xac-nhan-thong-tin-nop?sid=%s', SITE_ROOT, $data['sid']);
        }else{
            $url = sprintf('%sbo-cong-an/tiep-nhan-online/nhap-thong-tin-ho-so?sid=%s', SITE_ROOT, $data['sid']);
        }
        
        // Tham khảo 02_SourceCode/data/model/HoSo/BieuMau/DuLieu.php > function updateFromBieuMau.
        $form = FormBuilder::decodeXmlData($_POST['HoSoOnline_duLieuEform']);
        $efromId = get_post_var('HSOnline_EformID');
        
        if (!empty($efromId)) {
            $conn = Connection::getConnection();
            $conn->turnOffAutoCommit();
        }
        
        if ($form instanceof stdClass && is_array($form->data)) {
            for ($i = 0; $i < count($form->data); $i++) {
                $row = $form->data[$i];
                $name_thieu = $row->name;
                if( //!array_key_exists('CongDan_'.$name_thieu, $_POST) &&
                    !in_array(substr($name_thieu,0), $dsHSOnline)
                    && substr($name_thieu, 0,3) == '_fs') {
                    $kqInsThieu = (new Oracle\Package\BCA_DICH_VU_CONG())->INSERT_HS_DLHS_ONL_BCA([
                        'P_MA_HO_SO_ONLINE' => $hoSoOnline->getMaHoSo() == $data['sid'] ? $hoSoOnline->getMaHoSo() : null,
                        'P_SID' => $data['sid'],
                        'P_EFORM_ID' => $efromId,
                        'P_CONTROL_NAME' => $name_thieu,
                        'P_GIA_TRI' => is_array($row->value) ? json_encode($row->value) : $row->value,
                        'P_CHECKED' => isset($row->checked) ? $row->checked : null
                    ]);
                }
            }
        }
        
        if (!empty($efromId)) {
            $checkLuuDLHS = (new Oracle\Package\BCA_DICH_VU_CONG())->CHECK_DL_HO_SO_DUOC_LUU([
                'P_SID' => $hoSoOnline->getMaHoSo() == $data['sid'] ? null : $data['sid'],
                'P_MA_HO_SO_ONLINE' => $hoSoOnline->getMaHoSo() == $data['sid'] ? $hoSoOnline->getMaHoSo() : null,
                'P_EFORM_ID' => $efromId
            ]);

            if ($checkLuuDLHS == 0) {
                $conn->rollback();
                $applier->sessionSet(Applier::ER_MESSAGE, 'Dữ liệu biểu mẫu điền vào bị lỗi, công dân vui lòng nhập lại! Xin cảm ơn!');
                header(sprintf('Location: %sbo-cong-an/tiep-nhan-online/thong-bao-loi?sid=%s', SITE_ROOT, $data['sid']));
                exit;
            }
        }

        $hoSoOnline->merge(Entity\HoSoOnline::fromPost(), function ($prop) {
            return in_array($prop->name, [
                'maBieuMau'
                , 'duLieuBieuMau'
                , 'veViec'
                , 'ghiChu'
                , 'fileGiayToKhac'
                , 'giayToKhac'
            ]);
        });

        $congDan = $hoSoOnline->getCongDan();
        $congDan->merge(Entity\CongDan::fromPost(), function ($prop) {
            return in_array($prop->name, [
                'tenCongDan'
                , 'tenCoQuanToChuc'
                , 'soCmnd'
                , 'noiCapCmnd'
                , 'diDong'
                , 'fax'
                , 'email'
                , 'website'
                , 'maPhuongXa'
                , 'diaChi'
                , 'ngayCapCmnd'
                , 'phuongXa'
                , 'soGCNGP'
                , 'ngaySinh'
                , 'gioiTinh'
                , 'danToc'
                , 'maDMDiaChi'
                , 'maDMQuocGia'
                , 'diaChiNuocNgoai'
            ]);
        });
        $chuHoSo = new Entity\ChuHoSo();
        $chuHoSo->merge(Entity\ChuHoSo::fromPost(), function ($prop) {
            return in_array($prop->name, [
                'tenChuHoSo'
                , 'tenCoQuanToChucCHS'
                , 'maSoThueChuHoSo'
                , 'soCMNDChuHoSo'
                , 'ngayCapCMNDCHS'
                , 'noiCapCMNDCHS'
                , 'maTinhThanhCHS'
                , 'maQuanHuyenCHS'
                , 'maPhuongXaCHS'
                , 'diDongLienLacCHS'
                , 'ngaySinhChuHoSo'
                , 'gioiTinhChuHoSo'
                , 'danTocChuHoSo'
                , 'emailChuHoSo'
                , 'faxChuHoSo'
                , 'websiteChuHoSo'
                , 'diaChiChuHoSo'
            ]);
        });
//        print_r(Entity\ChuHoSo::fromPost()); exit;
        if(!filter_var($congDan->getWebsite(), FILTER_VALIDATE_URL)) $congDan->setWebsite('');
        $hoSoOnline->setCongDan($congDan);
        $hoSoOnline->setChuHoSo($chuHoSo);
        $hoSoOnline->setSoGCNGP($filter->filter($request->getPost('CongDan_soGCNGP')));
        $hoSoOnline->setNgayCapGCNGP($filter->filter($request->getPost('CongDan_ngayCapGCNGP')));
        $hoSoOnline->setNoiCapGCNGP($filter->filter($request->getPost('CongDan_noiCapGCNGP')));
        $hoSoOnline->setMaTinhCapCMND($filter->filter($request->getPost('CongDan_maTinhCapCMND')));
        $hoSoOnline->merge(Entity\HoSoOnline::fromPost(), function ($prop) {
            return $prop->name === 'maBieuMauNguoiNop';
        });
        /*******
         *
         *
         */
        $hoSoOnline->setDanhSachGiayToNop(DanhMuc\GiayToCuaHoSoOnline::fromPost(['lre_options' => ['BieuMau', 'Template']]));
        $giayToOld = $hoSoOnline->getDmGiayToKhac();
        $hoSoOnline->setDmGiayToKhac($this->makeDSGiayToKhac($hoSoOnline->getMaHoSo()));
        $applier->sessionSet(Applier::ET_HO_SO_ONLINE, $hoSoOnline);
        if (($maCaThi = get_post_var('caThiMaCaThi'))) {
            $soLuongDK = get_post_var('caThiSoLuongDangKy');
            if ($soLuongDK >= 0) {
                $applier->sessionSet('DL_CA_THI', [
                    'P_MA_CA_THI' => $maCaThi,
                    'P_SO_LUONG_DANG_KY' => $soLuongDK,
                    'P_SO_HO_SO' => '']);
            }
        }
        $coLienThongVilis = (int) Entity\System\Parameter::fromId('LIEN_THONG_VILIS')->getValue();
        if($coLienThongVilis){
            $hoSoVilis = Entity\HoSoVilis::fromPost([
                'source_type' => Entity\HoSoVilis::JSON_PROPERTIES,
                'lre_options' => ['PhuongXa' => ['QuanHuyen']]
            ]);
            $giayToDinhKemVilis = $this->call('vilis')->LayDanhSachGiayToHoSoVilis();
            $applier->sessionSet(Applier::HS_VILIS, $hoSoVilis);
            $applier->sessionSet(Applier::GT_VILIS, $giayToDinhKemVilis);
            $applier->sessionSet(Applier::XML_VILIS, get_post_var('quytrinhVilisXML','',false));
        }
        //Save log file giay to khac
        $giayto_old = [];
        $giayto_new = [];
        foreach ($giayToOld->getItems() as $giayto) {
            $arr = [];
            $arr['tenGiayTo'] = $giayto->getTenGiayTo();
            $arr['fileGiayTo'] = $giayto->getFileGiayTo();
            $giayto_old[] = $arr;
        }
        foreach ($hoSoOnline->getDmGiayToKhac()->getItems() as $giayto) {
            $arr = [];
            $arr['tenGiayTo'] = $giayto->getTenGiayTo();
            $arr['fileGiayTo'] = $giayto->getFileGiayTo();
            $giayto_new[] = $arr;
        }
        $log = new \Model\Entity\Log\LogThaoTacHoSo();
        $log->setTenCongDanThucHien($hoSoOnline->getCongDan()->getTenCongDan());
        $log->setTenThaoTac('[UPLOAD]-Cập nhật giấy tờ khác (TPHS)');
        $log->setModule('bo-cong-an/tiep-nhan-online/nhap-thong-tin-ho-so');
        $log->setMoTa('Cập nhật upload file giấy tờ khác (TPHS)');
        $log->setDuLieuGoc(json_encode($giayto_old, JSON_PRETTY_PRINT));
        $log->setDuLieuThayDoi(json_encode($giayto_new, JSON_PRETTY_PRINT));
        $log->insert();

        /*******
         *
         *
         */

        $applier->sessionSet(Applier::ET_HO_SO_ONLINE, $hoSoOnline);

        $hoSoOnline = $applier->sessionGet(Applier::ET_HO_SO_ONLINE);

        $SMS_XAC_THUC = (int) Entity\System\Parameter::fromId('DVC_SMS_XAC_THUC_NGUOI_NOP')->getValue();
        $EMAIL_XAC_THUC = (int) Entity\System\Parameter::fromId('DVC_EMAIL_XAC_THUC_NGUOI_NOP')->getValue();
        if ($SMS_XAC_THUC || $EMAIL_XAC_THUC) {
            $url = sprintf('Location: %sbo-cong-an/tiep-nhan-online/xac-nhan-ma-nop-ho-so?%s', SITE_ROOT, http_build_query([
                'sid' => $data['sid'],
                'return-url' => $url
            ]));
            $this->goiMaNopHoSo($congDan, $SMS_XAC_THUC, $EMAIL_XAC_THUC, $url, $hoSoOnline->getMaCoQuan()?:null);
        } else {
            header(sprintf('Location: %s', $url));
        }
        exit;
    }

    public function luuThongTinNguoiNopHoSo3b() {
        $request = $this->getRequest();
        $filter = $this->getFilter();
        $data = $this->getPostData();
        //fix bug security
        $csrf = new Csrf();
        if($csrf->isValid($filter->filter($request->getPost('tokencsrf')))){
            $session = $csrf->getSession();
            $session->getManager()->getStorage()->clear($csrf->getSessionName());
        }else{
            exit('Permission denied');
        }
        $dsHSOnline = [
            'tenCongDan'
            , 'tenCoQuanToChuc'
            , 'soCmnd'
            , 'noiCapCmnd'
            , 'diDong'
            , 'fax'
            , 'email'
            , 'website'
            , 'maPhuongXa'
            , 'diaChi'
            , 'ngayCapCmnd'
            , 'phuongXa'
            , 'soGCNGP'
            , 'ngaySinh'
            , 'gioiTinh'
            , 'danToc'
            , 'maDMDiaChi'
            , 'maDMQuocGia'
            , 'diaChiNuocNgoai'
            , 'phuongXa_maQuanHuyen'
            , 'phuongXa_quanHuyen_maTinhThanh'
            , 'ngaySinhCongDan'
            , 'gioiTinhCongDan'
            , 'danTocCongDan'
            , 'duLieuEform' // hienctt - gửi biểu mẫu dạng xml
        ];

        $url = sprintf('%sbo-cong-an/tiep-nhan-online/nhap-thong-tin-ho-so?sid=%s', SITE_ROOT, $data['sid']);
        $applier = new Applier($data['sid']);
        $hoSoOnline = $applier->sessionGet(Applier::ET_HO_SO_ONLINE);

        // hienctt 2317 Tham khảo 02_SourceCode/data/model/HoSo/BieuMau/DuLieu.php > function updateFromBieuMau.
        $form = FormBuilder::decodeXmlData($_POST['HoSoOnline_duLieuEform']);
        if ($form instanceof stdClass && is_array($form->data)) {
            for ($i = 0; $i < count($form->data); $i++) {
                $row = $form->data[$i];
                $name_thieu = $row->name;
                if( //!array_key_exists('CongDan_'.$name_thieu, $_POST) &&
                    !in_array(substr($name_thieu,0), $dsHSOnline)
                    && substr($name_thieu, 0,3) == '_fs') {
                    if ($name_thieu == '_fs_BCANguoiCungThayDoi') {
                        $dataTableNctd = [];
                        if (is_array($row->value)) {
                            foreach ($row->value as $itemNctd) {
                                if (!empty($itemNctd->FULLNAME)) {
                                    $dataTableNctd[] = $itemNctd;
                                }
                            }
                        }
                        $kqInsThieu = (new Oracle\Package\BCA_DICH_VU_CONG())->INSERT_HS_DLHS_ONL_BCA([
                            'P_MA_HO_SO_ONLINE' => $hoSoOnline->getMaHoSo() == $data['sid'] ? $hoSoOnline->getMaHoSo() : null,
                            'P_SID' => $data['sid'],
                            'P_EFORM_ID' => get_post_var('HSOnline_EformID'),
                            'P_CONTROL_NAME' => $name_thieu,
                            'P_GIA_TRI' => is_array($dataTableNctd) ? json_encode($dataTableNctd) : $dataTableNctd,
                            'P_CHECKED' => isset($row->checked) ? $row->checked : null
                        ]);
                    } else {
                        $kqInsThieu = (new Oracle\Package\BCA_DICH_VU_CONG())->INSERT_HS_DLHS_ONL_BCA([
                            'P_MA_HO_SO_ONLINE' => $hoSoOnline->getMaHoSo() == $data['sid'] ? $hoSoOnline->getMaHoSo() : null,
                            'P_SID' => $data['sid'],
                            'P_EFORM_ID' => get_post_var('HSOnline_EformID'),
                            'P_CONTROL_NAME' => $name_thieu,
                            'P_GIA_TRI' => is_array($row->value) ? json_encode($row->value) : $row->value,
                            'P_CHECKED' => isset($row->checked) ? $row->checked : null
                        ]);
                    }
                }
            }
        }

        $congDan = $hoSoOnline->getCongDan();
        $congDan->merge(Entity\CongDan::fromPost(), function ($prop) {
            return in_array($prop->name, [
                'tenCongDan'
                , 'tenCoQuanToChuc'
                , 'soCmnd'
                , 'noiCapCmnd'
                , 'diDong'
                , 'fax'
                , 'email'
                , 'website'
                , 'maPhuongXa'
                , 'diaChi'
                , 'ngayCapCmnd'
                , 'phuongXa'
                , 'soGCNGP'
                , 'ngaySinh'
                , 'gioiTinh'
                , 'danToc'
                , 'maDMDiaChi'
                , 'maDMQuocGia'
                , 'diaChiNuocNgoai'
            ]);
        });
        $chuHoSo = new Entity\ChuHoSo();
        $chuHoSo->merge(Entity\ChuHoSo::fromPost(), function ($prop) {
            return in_array($prop->name, [
                'tenChuHoSo'
                , 'tenCoQuanToChucCHS'
                , 'maSoThueChuHoSo'
                , 'soCMNDChuHoSo'
                , 'ngayCapCMNDCHS'
                , 'noiCapCMNDCHS'
                , 'maTinhThanhCHS'
                , 'maQuanHuyenCHS'
                , 'maPhuongXaCHS'
                , 'diDongLienLacCHS'
                , 'ngaySinhChuHoSo'
                , 'gioiTinhChuHoSo'
                , 'danTocChuHoSo'
                , 'emailChuHoSo'
                , 'faxChuHoSo'
                , 'websiteChuHoSo'
                , 'diaChiChuHoSo'
            ]);
        });
//        print_r(Entity\ChuHoSo::fromPost()); exit;
        if(!filter_var($congDan->getWebsite(), FILTER_VALIDATE_URL)) $congDan->setWebsite('');
        $hoSoOnline->setCongDan($congDan);
        $hoSoOnline->setChuHoSo($chuHoSo);
        $hoSoOnline->setSoGCNGP($filter->filter($request->getPost('CongDan_soGCNGP')));
        $hoSoOnline->setNgayCapGCNGP($filter->filter($request->getPost('CongDan_ngayCapGCNGP')));
        $hoSoOnline->setNoiCapGCNGP($filter->filter($request->getPost('CongDan_noiCapGCNGP')));
        $hoSoOnline->setMaTinhCapCMND($filter->filter($request->getPost('CongDan_maTinhCapCMND')));
        $hoSoOnline->merge(Entity\HoSoOnline::fromPost(), function ($prop) {
            return $prop->name === 'maBieuMauNguoiNop';
        });
        $applier->sessionSet(Applier::ET_HO_SO_ONLINE, $hoSoOnline);

        $hoSoOnline = $applier->sessionGet(Applier::ET_HO_SO_ONLINE);

        $SMS_XAC_THUC = (int) Entity\System\Parameter::fromId('DVC_SMS_XAC_THUC_NGUOI_NOP')->getValue();
        $EMAIL_XAC_THUC = (int) Entity\System\Parameter::fromId('DVC_EMAIL_XAC_THUC_NGUOI_NOP')->getValue();
        if ($SMS_XAC_THUC || $EMAIL_XAC_THUC) {
            $url = sprintf('Location: %sbo-cong-an/tiep-nhan-online/xac-nhan-ma-nop-ho-so?%s', SITE_ROOT, http_build_query([
                'sid' => $data['sid'],
                'return-url' => $url
            ]));
            $this->goiMaNopHoSo($congDan, $SMS_XAC_THUC, $EMAIL_XAC_THUC, $url, $hoSoOnline->getMaCoQuan()?:null);
        } else {
            header(sprintf('Location: %s', $url));
        }
        exit;
    }

    public function luuThongTinHoSo() {
        $data = $this->getPostData();

        $applier = new Applier($data['sid']);
        $hoSoOnline = $applier->sessionGet(Applier::ET_HO_SO_ONLINE);
        $hoSoOnline->merge(Entity\HoSoOnline::fromPost(), function ($prop) {
            return in_array($prop->name, [
                'maBieuMau'
                , 'duLieuBieuMau'
                , 'veViec'
                , 'ghiChu'
                , 'fileGiayToKhac'
                , 'giayToKhac'
            ]);
        });
        $hoSoOnline->setDanhSachGiayToNop(DanhMuc\GiayToCuaHoSoOnline::fromPost(['lre_options' => ['BieuMau', 'Template']]));
        $giayToOld = $hoSoOnline->getDmGiayToKhac();
        $hoSoOnline->setDmGiayToKhac($this->makeDSGiayToKhac($hoSoOnline->getMaHoSo()));
        $applier->sessionSet(Applier::ET_HO_SO_ONLINE, $hoSoOnline);
        if (($maCaThi = get_post_var('caThiMaCaThi'))) {
            $soLuongDK = get_post_var('caThiSoLuongDangKy');
            if ($soLuongDK >= 0) {
                $applier->sessionSet('DL_CA_THI', [
                    'P_MA_CA_THI' => $maCaThi,
                    'P_SO_LUONG_DANG_KY' => $soLuongDK,
                    'P_SO_HO_SO' => '']);
            }
        }
        $coLienThongVilis = (int) Entity\System\Parameter::fromId('LIEN_THONG_VILIS')->getValue();
        if($coLienThongVilis){
            $hoSoVilis = Entity\HoSoVilis::fromPost([
                'source_type' => Entity\HoSoVilis::JSON_PROPERTIES,
                'lre_options' => ['PhuongXa' => ['QuanHuyen']]
            ]);
            $giayToDinhKemVilis = $this->call('vilis')->LayDanhSachGiayToHoSoVilis();
            $applier->sessionSet(Applier::HS_VILIS, $hoSoVilis);
            $applier->sessionSet(Applier::GT_VILIS, $giayToDinhKemVilis);
            $applier->sessionSet(Applier::XML_VILIS, get_post_var('quytrinhVilisXML','',false));
        }
        //Save log file giay to khac
        $giayto_old = [];
        $giayto_new = [];
        foreach ($giayToOld->getItems() as $giayto) {
            $arr = [];
            $arr['tenGiayTo'] = $giayto->getTenGiayTo();
            $arr['fileGiayTo'] = $giayto->getFileGiayTo();
            $giayto_old[] = $arr;
        }
        foreach ($hoSoOnline->getDmGiayToKhac()->getItems() as $giayto) {
            $arr = [];
            $arr['tenGiayTo'] = $giayto->getTenGiayTo();
            $arr['fileGiayTo'] = $giayto->getFileGiayTo();
            $giayto_new[] = $arr;
        }
        $log = new \Model\Entity\Log\LogThaoTacHoSo();
        $log->setTenCongDanThucHien($hoSoOnline->getCongDan()->getTenCongDan());
        $log->setTenThaoTac('[UPLOAD]-Cập nhật giấy tờ khác (TPHS)');
        $log->setModule('bo-cong-an/tiep-nhan-online/nhap-thong-tin-ho-so');
        $log->setMoTa('Cập nhật upload file giấy tờ khác (TPHS)');
        $log->setDuLieuGoc(json_encode($giayto_old, JSON_PRETTY_PRINT));
        $log->setDuLieuThayDoi(json_encode($giayto_new, JSON_PRETTY_PRINT));
        $log->insert();
        //End save log
        // Lưu ngày hẹn, buổi hẹn
        if (!empty($data['bcangayhen'])) {
            $key_ngay_hen = Entity\System\Parameter::fromId('KEY_BCA_IDS_NGAY_HEN')->getValue();
            $luu_ngay_hen = (new \Oracle\Package\BCA_DBLT())->UPDATE_THEO_MTC_VA_SID([
                'P_SID' => $data['sid'],
                'P_GIA_TRI' => $data['bcangayhen'],
                'P_MA_TIEU_CHI_SEARCH' => (!empty($key_ngay_hen)) ? $key_ngay_hen : 'IDS_BCA_NGAY_HEN',//IDS_BCA_NGAY_HEN
            ]);
        }
//        if (!empty($data['bcabuoihen']) && !empty($data['bcangayhen']) && (int) Entity\System\Parameter::fromId('BCA_SERVICE_ACTIVE')->getValue() === 1) {
//            $key_ngay_hen = Entity\System\Parameter::fromId('KEY_BCA_IDS_NGAY_HEN')->getValue();
//            $key_buoi_hen = Entity\System\Parameter::fromId('KEY_BCA_IDS_TIME_HEN')->getValue();
//            $luu_ngay_hen = (new \Oracle\Package\BCA_DBLT())->UPDATE_THEO_MTC_VA_SID([
//                'P_SID' => $data['sid'],
//                'P_GIA_TRI' => $data['bcangayhen'],
//                'P_MA_TIEU_CHI_SEARCH' => (!empty($key_ngay_hen)) ? $key_ngay_hen : 'IDS_BCA_NGAY_HEN',//IDS_BCA_NGAY_HEN
//            ]);
//            $luu_buoi_hen = (new \Oracle\Package\BCA_DBLT())->UPDATE_THEO_MTC_VA_SID([
//                'P_SID' => $data['sid'],
//                'P_GIA_TRI' => $data['bcabuoihen'],
//                'P_MA_TIEU_CHI_SEARCH' => (!empty($key_buoi_hen)) ? $key_buoi_hen : 'IDS_BCA_TIME_HEN',//IDS_BCA_BUOI_HEN
//            ]);
//        }
        $MA_THU_TUC = $hoSoOnline->getThuTuc()->getMaThuTuc();
        $tsqt_nop_hs_2b = Model\Entity\System\Parameter::fromId('BCA_CAU_HINH_TT_QUY_TRINH_NOP_HS_HAI_BUOC')->getValue();
        $tsqt_nop_hs_3b = Model\Entity\System\Parameter::fromId('BCA_CAU_HINH_TT_QUY_TRINH_NOP_HS_BA_BUOC')->getValue();
        $qt_nop_hs_2b = $tsqt_nop_hs_2b ? explode(',', $tsqt_nop_hs_2b) : [];
        $qt_nop_hs_3b = $tsqt_nop_hs_3b ? explode(',', $tsqt_nop_hs_3b) : [];

        if(in_array($MA_THU_TUC, $qt_nop_hs_2b) || in_array($MA_THU_TUC, $qt_nop_hs_3b)){
            header(sprintf('Location: %sbo-cong-an/tiep-nhan-online/xac-nhan-thong-tin-nop-3b?sid=%s', SITE_ROOT, $data['sid']));
            exit;
        }else{
            header(sprintf('Location: %sbo-cong-an/tiep-nhan-online/nhap-le-phi-ho-so?sid=%s', SITE_ROOT, $data['sid']));
            exit;
        }
        
    }

    public function luuLePhiHoSo() {
        $data = $this->getPostData();
        $applier = new Applier($data['sid']);
        $hoSoOnline = $applier->sessionGet(Applier::ET_HO_SO_ONLINE);
        if((int) Entity\System\Parameter::fromId('DOI_TUONG_GIAM_CUOC_VNPOST')->getValue() === 1) { //IGATESUPP-26470 tttruong-kv1
            $paramMienGiam = [
                'madt' => $this->getRequest()->getPost('loaidt_vnpost'),
                'tienphaitra' => $this->getRequest()->getPost('txt_tienDoiTuongGiam')
            ];
            $applier->sessionSet('DT_MG_VNPOST',json_encode($paramMienGiam));
        }
        $hoSoOnline->merge(Entity\HoSoOnline::fromPost([
            'source_type' => Entity\HoSoOnline::JSON_PROPERTIES
        ]), function ($prop) {
            return in_array($prop->name, [
                'maPhuongXaThuGom'
                , 'maHinhThucNop'
                , 'diaChiThuGom'
                , 'maHinhThucNhanKetQua'
                , 'maPhuongXaNhanKetQua'
                , 'diaChiNhanKetQua'
                , 'ngayYeuCauThuGom'
                , 'maHinhThucThanhToan'
                , 'soHoaDonThanhToan'
                , 'maCNNganHangThanhToan'
                , 'maBuuCucThanhToan'
                , 'smartGatePaymentRequestId'
                , 'phuongXaThuGom'
                , 'phuongXaNhanKetQua'
                , 'hinhThucNop'
                , 'hinhThucNhanKetQua'
                , 'CNNganHangThanhToan'
                , 'buuCucThanhToan'
                , 'hinhThucThanhToan'
                , 'dmLePhiHoSo'
                , 'fileDaThanhToan'
            ]);
        });
        $tenNguoiNhanKetQuaVpc = strval($data["tenNguoiNhanKetQuaVpc"]);
        $sdtNguoiNhanKetQuaVpc = strval($data["sdtNguoiNhanKetQuaVpc"]);
        $hoSoOnline->settenNguoiNhanKetQuaVpc($tenNguoiNhanKetQuaVpc);
        $hoSoOnline->setsdtNguoiNhanKetQuaVpc($sdtNguoiNhanKetQuaVpc);
//        $applier->sessionSet('VNPOSTVPC',json_encode($tenNguoiNhanKetQuaVpc1));
        $applier->sessionSet(Applier::ET_HO_SO_ONLINE, $hoSoOnline);
        header(sprintf('Location: %sbo-cong-an/tiep-nhan-online/xac-nhan-thong-tin-nop-moi?sid=%s', SITE_ROOT, $data['sid']));
        exit;
    }

    public function luuHoSo() {
        $conn = Connection::getConnection();
        $conn->turnOffAutoCommit();
        $data = $this->getPostData();
        $applier = new Applier($data['sid']);
        $hoSoOnline = $applier->sessionGet(Applier::ET_HO_SO_ONLINE);
        $hoSoOnline->setTrangThaiHoSo('LUU_NHUNG_KHONG_NOP');
        $resultInfo = $hoSoOnline->updatePack1();
        if ($resultInfo->getCode() === 1) {
            $conn->commit();
            $applier->sessionSet(Applier::ET_HO_SO_ONLINE, $hoSoOnline);
            header(sprintf('Location: %sbo-cong-an/tiep-nhan-online/da-luu-ho-so?sid=%s', SITE_ROOT, $data['sid']));
            exit;
        }
        $conn->rollback();
        exit(sprintf('[Error] %s', $resultInfo->getMessage()));
    }

    public function nopHoSo() {
        $conn = Connection::getConnection();
        $conn->turnOffAutoCommit();
        $data = $this->getPostData();
        $applier = new Applier($data['sid']);
        $hoSoOnline = $applier->sessionGet(Applier::ET_HO_SO_ONLINE);
        $countDaThanhToan = 0;
        $countChuaThanhToan = 0;
        $phiChuaThanhToan = 0;
        if (empty($hoSoOnline)) {
            $conn->rollback();
            $applier->sessionSet(Applier::ER_MESSAGE, 'Thời gian thao tác nộp hồ sơ quá lâu. Vui lòng thao tác nộp lại hồ sơ!');
            header(sprintf('Location: %sbo-cong-an/tiep-nhan-online/thong-bao-loi?sid=%s', SITE_ROOT, $data['sid']));
            exit;
        }
        // Lưu ngày hẹn, buổi hẹn
        if (!empty($data['bcangayhen'])) {
            $key_ngay_hen = Entity\System\Parameter::fromId('KEY_BCA_IDS_NGAY_HEN')->getValue();
            $key_buoi_hen = Entity\System\Parameter::fromId('KEY_BCA_IDS_TIME_HEN')->getValue();
            if ($hoSoOnline->getMaHoSo() == $data['sid']) {
                $luu_ngay_hen = (new \Oracle\Package\BCA_DBLT())->UPDATE_THEO_MTC_VA_MA_HS([
                    'P_SID' => $data['sid'],
                    'P_GIA_TRI' => $data['bcangayhen'],
                    'P_MA_TIEU_CHI_SEARCH' => (!empty($key_ngay_hen)) ? $key_ngay_hen : 'IDS_BCA_NGAY_HEN',//IDS_BCA_NGAY_HEN
                ]);
                if (!empty($data['bcabuoihen'])) {
                    $luu_buoi_hen = (new \Oracle\Package\BCA_DBLT())->UPDATE_THEO_MTC_VA_MA_HS([
                        'P_SID' => $data['sid'],
                        'P_GIA_TRI' => $data['bcabuoihen'],
                        'P_MA_TIEU_CHI_SEARCH' => (!empty($key_buoi_hen)) ? $key_buoi_hen : 'IDS_BCA_TIME_HEN',//IDS_BCA_BUOI_HEN
                    ]);
                }
            } else {
                $luu_ngay_hen = (new \Oracle\Package\BCA_DBLT())->UPDATE_THEO_MTC_VA_SID([
                    'P_SID' => $data['sid'],
                    'P_GIA_TRI' => $data['bcangayhen'],
                    'P_MA_TIEU_CHI_SEARCH' => (!empty($key_ngay_hen)) ? $key_ngay_hen : 'IDS_BCA_NGAY_HEN',//IDS_BCA_NGAY_HEN
                ]);
                if (!empty($data['bcabuoihen'])) {
                    $luu_buoi_hen = (new \Oracle\Package\BCA_DBLT())->UPDATE_THEO_MTC_VA_SID([
                        'P_SID' => $data['sid'],
                        'P_GIA_TRI' => $data['bcabuoihen'],
                        'P_MA_TIEU_CHI_SEARCH' => (!empty($key_buoi_hen)) ? $key_buoi_hen : 'IDS_BCA_TIME_HEN',//IDS_BCA_BUOI_HEN
                    ]);
                }
            }

        }
        foreach ($hoSoOnline->getDmLePhiHoSo()->getItems() as $value) {
            if ($value->getSoLuong() === '' or $value->getSoLuong() === null or $value->getSoLuong() == 0 ) {
                exit('Số lượng của lệ phí phải lớn hơn hoặc bằng 1!');
            }
            if ($value->getDaThanhToan() == 1) {
                $countDaThanhToan++;
            }

            if ($value->getBatBuocThanhToan() == 1 && $value->getDaThanhToan() == 0) {
                $phiChuaThanhToan = $phiChuaThanhToan + ($value->getMucLePhi() * $value->getSoLuong());
                $countChuaThanhToan++;
            }
        }
        $checkNopMoi = 0; // 0: nop moi, 1: cap nhat
        if (!empty($hoSoOnline->getMaHoSo())) {
            $checkNopMoi = 1;
            $hoSoOnlineLog = Entity\HoSoOnline::fromMaHoSo($hoSoOnline->getMaHoSo());
        }

        $hoSoOnline->setTrangThaiHoSo('DA_NOP');
        $resultInfo = $hoSoOnline->updatePack1();

        if ($hoSoOnline->getKhoaCapNhat()) {
            $applier->sessionSet(Applier::KHOA_CAP_NHAT, $hoSoOnline->getKhoaCapNhat());
        }

        if(isset($hoSoOnlineLog) && $hoSoOnlineLog->getNgayYCBS()){
            $hoSoOnline->updateTrangThaiHoSo('DA_BO_SUNG_VA_NOP_LAI');
        }
        $dLCaThi = $applier->sessionGet('DL_CA_THI');
        // Kiểm tra nếu thủ tục có sử dụng biểu mẫu ca thi thì chạy vào hàm update ca thi
        if($dLCaThi != null && $dLCaThi != '' && count($dLCaThi) > 0){
            $updateCaThi = $this->updateSoLuongCaThi($hoSoOnline->getSoHoSo(),$data['sid']);
            if($updateCaThi!=1){
                // nếu update không thành công thì chuyển về bước 2 để nhập lại thông tin ca thi
                header(sprintf('Location: %sbo-cong-an/tiep-nhan-online/nhap-thong-tin-ho-so?sid=%s&ms=1', SITE_ROOT, $data['sid']));
                exit;
            }
        }

        // Begin: lưu HS vilis
        $coLienThongVilis =  Entity\System\Parameter::fromId('LIEN_THONG_VILIS')->getValue();
        if($resultInfo->getCode() === 1 && $coLienThongVilis && $applier->sessionGet(Applier::XML_VILIS)){
            $hoSoVilis = $applier->sessionGet(Applier::HS_VILIS);
            $maVilis = (new Oracle\Package\MC_VILIS())->LAY_MA_QUY_TRINH_VILIS([
                'P_MA_QUAN_HUYEN' => $hoSoVilis->getMaQuanHuyenDuocTn(),
                'P_MA_QT_MC' => (int) $hoSoOnline->getMaQttt(),
            ]);
            $hoSoVilis->setMaVilis($maVilis);
            if ($hoSoVilis->getMaVilis()) {
                if ($checkNopMoi === 0) { // nộp mới
                    $resultInfo = $this->insertVilis($applier,$hoSoOnline,$hoSoVilis);
                }
                else { // cập nhật
                    $resultInfo = $this->updateVilis($applier,$hoSoOnline,$hoSoVilis);
                }
            }
        }
        // End: lưu HS vilis

        // thêm thông tin chủ hồ sơ
        if($resultInfo->getCode() === 1 && Entity\System\Parameter::fromId('BM_HIEN_THI_CHU_HO_SO')->getValue() == 1) {
            $thongTinChuHoSo = $hoSoOnline->getChuHoSo();
            if ($thongTinChuHoSo != null) {
                $thongTinChuHoSo->setMaHoSoOnline($hoSoOnline->getMaHoSo());
                if (!$thongTinChuHoSo->update()) {
                    $resultInfo = new ResultInfo(0,'Thêm thông tin chủ hồ sơ thất bại');
                }
            }
        }
        // kết thúc thông tin chủ hồ sơ

        if ($resultInfo->getCode() === 1) {

            //cập nhật lại mã hồ sơ tại hs_du_lieu_ho_so $hoSoOnline->getMaHoSo()
            $kq = (new Oracle\Package\BCA_DICH_VU_CONG())->INSERT_HS_DLHS_ONL_BCA([
                'P_MA_HO_SO_ONLINE'=> $hoSoOnline->getMaHoSo(),
                'P_SID' => $data['sid'],
                'P_GIA_TRI' => '{"oldver":1,"name":null,"value":null}'
            ]);
            
//            $checkLuuDLHS = (new Oracle\Package\BCA_DICH_VU_CONG())->CHECK_DL_HO_SO_DUOC_LUU([
//                'P_MA_HO_SO_ONLINE' => $hoSoOnline->getMaHoSo()
//            ]);
            
//            if ($checkLuuDLHS == 0) {
//                $conn->rollback();
//                $applier->sessionSet(Applier::ER_MESSAGE, 'Có lỗi trong quá trình nhập thông tin, công dân vui lòng nhập lại! Xin cảm ơn');
//                header(sprintf('Location: %sbo-cong-an/tiep-nhan-online/thong-bao-loi?sid=%s', SITE_ROOT, $data['sid']));
//                exit;
//            }

//            $conn->commit();
//            $conn->turnOnAutoCommit();
            
            // Cập nhật mức độ
            if ($hoSoOnline->getMaHoSo() != '') {
                $this->call('hososingle');
                $this->hososingle->UPDATE_MUC_DO_HO_SO(null, $hoSoOnline->getMaHoSo());
            }

            $hoSoOnline->updateDmGiayToKhac(true);
            // 2018.06.14 tqdung - log nop ho so online - 0001
            $package = new LogData\Package();
            $content = new LogData\Content();
            if ($checkNopMoi == 0) {
                $package->setContent($content->createContentFromHoSoOnline($hoSoOnline, '0001'));
                $package->setContentId($hoSoOnline->getMaHoSo());
                $package->setDescription('Hồ sơ online mới');
                $package->setDaLenTruc(0);
                $package->setPackageTypeId('0001');
                $package->update();
            } else {
                $package->setContent($content->createContentFromHoSoOnline($hoSoOnline, '0002'));
                $package->setContentId($hoSoOnline->getMaHoSo());
                $package->setDescription('Cật nhật hồ sơ online');
                $package->setDaLenTruc(0);
                $package->setPackageTypeId('0002');
                $package->update();
            }

            // 20190804 - nop hs online - tqdung - notification
            $turn_on_notification = \Model\Entity\System\Parameter::fromId('TURN_ON_NOTIFICATION')->getValue();
            if ($turn_on_notification == 1) {
                $thongBao = new Entity\CanBoThongBao();
                $thongBao->setMaHoSoOnline($hoSoOnline->getMaHoSo());
                $thongBao->setNoiDungThongBao('Người dân vừa nộp hồ sơ online ' . $hoSoOnline->getSoHoSo());
                $thongBao->updateThongBao('', $hoSoOnline->getMaDonViTiepNhan(), $hoSoOnline->getMaQttt(), []);
            }

            if(Model\Entity\System\Parameter::fromId('HAN_NOP_LE_PHI_CA_THI')->getValue()){
                $this->updateCaThiHanNopLePhi($hoSoOnline->getMaHoSo());
            }
            
            // Lien thong Bo Cong An
            if(Entity\System\Parameter::fromId('BCA_SERVICE_ACTIVE',['cache' => false])->getValue() == 1){
                $congDanSession = Entity\CongDan::fromSession();
                $tinhTrangHoSoBCA = new Entity\BoCongAn\TinhTrangHoSo();
                $tinhTrangHoSoBCA->setSoHoSo($hoSoOnline->getSoHoSo());
                $tinhTrangHoSoBCA->setMaTinhTrang(Entity\BoCongAn\TinhTrangHoSo::CTN);
                $tinhTrangHoSoBCA->setCongDanCapNhat($congDanSession->getMaCongDan());
                $maPMLienThong = (New Package\BCA_LIEN_THONG())->GET_MA_PM_LT_QTTT([
                    'P_MA_THU_TUC' => $hoSoOnline->getMathutuc(),
                ]);
                $tinhTrangHoSoBCA->setMaPhanMemLT($maPMLienThong);
                $tinhTrangHoSoBCA->update();
            }
            if ($hoSoOnline->phaiThanhToanTrucTuyen()) {
                System\Logger::add('Công dân thanh toán online', ['entity' => $hoSoOnline]);
                $hoSoOnline->updateTrangThaiHoSo('DANG_CAP_NHAT');
                if ($phiChuaThanhToan <= 0) {
                    exit('Số tiền nhỏ hơn hoặc bằng 0, không thể thanh toán trực tuyến!');
                }
                if ($hoSoOnline->phaiThanhToanQuaSmartGate()) {
                    if ($phiChuaThanhToan >= Entity\HinhThucThanhToan::fromMaHinhThuc(5)->getPhiThanhToan()){
                        $smartGatePaymentRequest = Entity\SmartGate\PaymentRequest::fromHoSoOnline($hoSoOnline);
                        $uri = $this->getRequest()->getUri();
                        $root = $uri->getScheme() . '://' . $uri->getHost() . SITE_ROOT . 'bo-cong-an/tiep-nhan-online';
                        $smartGatePaymentRequest->setReturnURL($root . 'ket-qua-giao-dich-smartgate?sid=' . $data['sid']);
                        $smartGatePaymentRequest->setCancelURL($root . 'huy-giao-dich-smartgate?sid=' . $data['sid']);
                        if ($smartGatePaymentRequest->update()) {
                            $hoSoOnline->updateSmartGatePaymentRequestId($smartGatePaymentRequest->getOrderId());
                            $hoSoOnline->setSmartGatePaymentRequest($smartGatePaymentRequest);
                            $applier->sessionSet(Applier::ET_HO_SO_ONLINE, $hoSoOnline);
                            $applier->sessionSet(Applier::KHOA_CAP_NHAT, $hoSoOnline->getKhoaCapNhat());
                            header(sprintf('Location: %s', $smartGatePaymentRequest->createPaymentURL()));
                            exit;
                        }
                        exit('Some errors occurred, SmartGate can not serve!');
                    } else {
                        exit('Lệ phí chưa đủ để thanh toán qua VNPT SmartGate!');
                    }
                }
                if ($hoSoOnline->phaiThanhToanQuaVNPTPay()) {
                    if ($phiChuaThanhToan >= Entity\HinhThucThanhToan::fromMaHinhThuc(7)->getPhiThanhToan()){
                        $vnptPayInitRequest = Entity\VNPTPay\InitRequest::fromHoSoOnline($hoSoOnline);
                        if ($vnptPayInitRequest->update() && $vnptPayInitRequest->send()) {
                            $hoSoOnline->updateVNPTPayInitRequestId($vnptPayInitRequest->getMerchantOrderId());
                            $hoSoOnline->setVNPTPayInitRequest($vnptPayInitRequest);
                            $applier->sessionSet(Applier::ET_HO_SO_ONLINE, $hoSoOnline);
                            $applier->setId($vnptPayInitRequest->getMerchantOrderId());
                            $applier->sessionSet(Applier::ET_HO_SO_ONLINE, $hoSoOnline);
                            $applier->sessionSet(Applier::KHOA_CAP_NHAT, $hoSoOnline->getKhoaCapNhat());
                            header(sprintf('Location: %s', $vnptPayInitRequest->getInitResponse()->getRedirectURL()));
                            exit;
                        }
                        exit('Some errors occurred, VNPT Pay can not serve!');
                    } else {
                        exit('Lệ phí chưa đủ để thanh toán qua VNPT Pay!');
                    }
                }
                if ($hoSoOnline->phaiThanhToanQuaBIDV()) {
                    if ($phiChuaThanhToan >= Entity\HinhThucThanhToan::fromMaHinhThuc(11)->getPhiThanhToan()){
                        $BIDVInitRequest = Entity\BIDV\InitRequest::fromHoSoOnline($hoSoOnline);
                         if ($BIDVInitRequest->update() && $BIDVInitRequest->send()) {
                            $BIDVInitRequest->updateThanhToanBIDV();
                            //$hoSoOnline->setBIDVInitRequestId($BIDVInitRequest);
                            //$applier->sessionSet(Applier::ET_HO_SO_ONLINE, $hoSoOnline);
                            $applier->setId($BIDVInitRequest->getTransId());
                            $applier->sessionSet(Applier::ET_HO_SO_ONLINE, $hoSoOnline);
                            $applier->sessionSet(Applier::KHOA_CAP_NHAT, $hoSoOnline->getKhoaCapNhat());
                            header(sprintf('Location: %s', $BIDVInitRequest->getInitResponse()->getRedirectURL()));
                            exit;
                         }
                        exit('Some errors occurred, BIDV can not serve!');
                    } else {
                        exit('Lệ phí chưa đủ để thanh toán qua BIDV!');
                    }
                }
                if ($hoSoOnline->phaiThanhToanQuaVNAY()) {
                    if ($phiChuaThanhToan >= Entity\HinhThucThanhToan::fromMaHinhThuc(8)->getPhiThanhToan()){
                        $vnPayInitUrlRequest = Entity\VNPay\InitUrlRequest::fromHoSoOnline($hoSoOnline, 'new');
                        $vnPayInitUrlRequest->createUrl();

                        if ($vnPayInitUrlRequest->update() && $vnPayInitUrlRequest->updateThanhToanVNPay()) {
                            // $hoSoOnline->setVNPayInitRequest($vnPayInitUrlRequest);
                            // $applier->sessionSet(Applier::ET_HO_SO_ONLINE, $hoSoOnline);
                            $applier->setId($vnPayInitUrlRequest->getTxnRef());
                            $applier->sessionSet(Applier::ET_HO_SO_ONLINE, $hoSoOnline);
                            $applier->sessionSet(Applier::KHOA_CAP_NHAT, $hoSoOnline->getKhoaCapNhat());
                            header(sprintf('Location: %s', $vnPayInitUrlRequest->getUrlRequest()));
                            exit;
                        }
                        exit('Some errors occurred, VNPay can not serve!');
                    } else {
                        exit('Lệ phí chưa đủ để thanh toán qua VNPay!');
                    }
                }
                if ($hoSoOnline->phaiThanhToanQuaVietinBank()) {
                    $hoSoOnline->updateTrangThaiHoSo('DA_NOP');
                    $responseVietinbank = $this->thanhToanVietinBank($hoSoOnline);
                }
                $check_thanhtoan_PP_TanDan =Entity\System\Parameter::fromId('PAYMENT_PLATFORM_TAN_DAN_ACTIVE')->getValue();

                if ($hoSoOnline->phaiThanhToanQuaPaymentPlatform()) {
                    if ($phiChuaThanhToan >= Entity\HinhThucThanhToan::fromMaHinhThuc(10)->getPhiThanhToan()){
                        if ($check_thanhtoan_PP_TanDan==1) {
                           $paymentPlatform = Entity\PaymentPlatform\InitRequestPaymentPlatformTanDan::fromHoSoOnline($hoSoOnline);
                        }
                        else{
                            $paymentPlatform = Entity\PaymentPlatform\InitRequestPaymentPlatform::fromHoSoOnline($hoSoOnline);
                        }

                    //    dump_die($paymentPlatform);

                        if ($paymentPlatform->update() && $paymentPlatform->send()) {

                            $applier->setId($paymentPlatform->getMaThamChieu());
                            $applier->sessionSet(Applier::ET_HO_SO_ONLINE, $hoSoOnline);
                            $applier->sessionSet(Applier::KHOA_CAP_NHAT, $hoSoOnline->getKhoaCapNhat());
                            header(sprintf('Location: %s', $paymentPlatform->getInitResponse()->getUrlThanhToan()));
                            exit;
                        }

                        exit('Some errors occurred, Payment Platform can not serve!');
                    } else {
                        exit('Lệ phí chưa đủ để thanh toán qua Payment Platform!');
                    }
                }
                // dump_die($hoSoOnline->phaiThanhToanQuaPaymentPlatform());

                //vqhuy.lan - PayGov
                if ($hoSoOnline->phaiThanhToanQuaPayGov()) {
                    if ($phiChuaThanhToan >= Entity\HinhThucThanhToan::fromMaHinhThuc(21)->getPhiThanhToan()) {
                        $payGov = Entity\PayGov\YeuCauThanhToanPayGov::fromHoSoOnline($hoSoOnline);
                        System\Logger::add('Công dân thanh toán payGov', ['entity' => $payGov]);
                        if ($payGov->update() && $payGov->send()) {
                            $applier->setId($payGov->getMaYeuCauThanhToan());
                            $applier->sessionSet(Applier::ET_HO_SO_ONLINE, $hoSoOnline);
                            $applier->sessionSet(Applier::KHOA_CAP_NHAT, $hoSoOnline->getKhoaCapNhat());
                            header(sprintf('Location: %s', $payGov->getYeuCauResponse()->getUrlThanhToan()));
                            exit;
                        }

                        exit('Some errors occurred, PayGov can not serve!');
                    } else {
                        exit('Lệ phí chưa đủ để thanh toán qua PayGov!');
                    }
                }
                //end vqhuy.lan
                if ($hoSoOnline->phaiThanhToanPayGov()) {
                    if ($phiChuaThanhToan >= Entity\HinhThucThanhToan::fromMaHinhThuc(14)->getPhiThanhToan()){
                        $initRequestPayGov = Entity\PayGov\InitRequestPayGov::fromHoSoOnline($hoSoOnline);

                        if ($initRequestPayGov->update() && $initRequestPayGov->send()) {
                            // $hoSoOnline->setVNPayInitRequest($initRequestPayGov);
                            // $applier->sessionSet(Applier::ET_HO_SO_ONLINE, $hoSoOnline);
                            $applier->setId($initRequestPayGov->getOrderId());
                            $applier->sessionSet(Applier::ET_HO_SO_ONLINE, $hoSoOnline);
                            $applier->sessionSet(Applier::KHOA_CAP_NHAT, $hoSoOnline->getKhoaCapNhat());
                            if ($initRequestPayGov->getInitResponse()->getErrorCode() == 'SUCCESSFUL') {
                                header(sprintf('Location: %s', $initRequestPayGov->getInitResponse()->getUrl()));
                                exit;
                            } else {
                                exit(sprintf('Error code: %s, Error message: %s', $initRequestPayGov->getInitResponse()->getErrorCode(), $initRequestPayGov->getInitResponse()->getErrorMessage()));
                            }
                        }
                        exit('Some errors occurred, PayGov can not serve!');
                    } else {
                        exit('Lệ phí chưa đủ để thanh toán qua PayGov!');
                    }
                }
            }
            System\Logger::add('Công dân nộp hồ sơ online',['entity' => $hoSoOnline]);

            $hoSoOnline->guiThongBaoDaNop();
            $hoSoOnline->setDiDongLienLacCongDan($hoSoOnline->getCongDan()->getDiDong());
            $sendZaloNotificationFlag = Entity\System\Parameter::fromId('ZALO_SEND_MESSAGE_FLAG', ['cache' => false])->getValue();
            if((int)$sendZaloNotificationFlag == 1){
            if (!empty($hoSoOnline->getCongDan()->getDiDong())) {
                $cauHinhZaloNopOnline = (new \Oracle\StoreProcedure\SELECT_CONTENT_ZALO_QTTT([
                    'P_MA_QTTT' => $hoSoOnline->getMaQTTT(),
                    'P_TYPE' => 1 //Nộp hồ sơ online
                ]))->getDefaultResult(0);
                if($cauHinhZaloNopOnline['CONTENT']){
                    $zalo = new Entity\Zalo\ZaloNotificationV2();
                    $zalo->loadConfig($hoSoOnline->getMaCoQuan());
                    $zalo->setHoSo($hoSoOnline);
                    if($zalo->checkUserId()){
                        $noiDungZalo = $hoSoOnline->replaceVariableHS($cauHinhZaloNopOnline['CONTENT']);
                        $zalo->setMessageMode(Entity\Zalo\ZaloNotificationV2::MESSAGE_MODE_APPLIED);
                        $zalo->setMessageBody($noiDungZalo);
                        $logZalo = new \Oracle\OracleFunction\INSERT_LOG_THONG_BAO(array(
                            'P_MA_HO_SO_ONLINE' => $hoSoOnline->getMaHoSo() ?: '',
                            'P_MA_CAN_BO_THUC_HIEN' => '',
                            'P_MA_THAO_TAC' => 6,
                            'P_NOI_DUNG' => $noiDungZalo ?: ''
                        ));
                        $notifyResult = $zalo->sendEDocStatusMessage();
                        Model\System\Logger::add('Gửi zalo đến hồ sơ: ' . $hoSoOnline->getSoHoSo() . ' SĐT: ' . $hoSoOnline->getDiDongLienLacCongDan() . ' : ' . json_encode($notifyResult));
                    }else{
                        $notifyResult = $zalo->sendInviteMessage();
                    }
                }else{
                    $zalo = new Entity\Zalo\ZaloNotificationV2();
                    $zalo->loadConfig($hoSoOnline->getMaCoQuan());
                    $hsOnline = \Model\Entity\HoSoOnline::fromSoHoSo($hoSoOnline->getSoHoSo());
                    $hoSoTemp = \Model\Entity\HoSo::fromHoSoOnline($hsOnline);
                    $zalo->setHoSo($hoSoTemp);
                    if ($zalo->checkUserId()) {
                        $zalo->setMessageMode(Entity\Zalo\ZaloNotificationV2::MESSAGE_MODE_APPLIED);
                        $notifyResult = $zalo->sendNotifyMessage();
                        $this->INSERT_LOG('Gửi zalo đến hồ sơ: ' . $hoSoTemp->getSoHoSo() . ' SĐT: ' . $hoSoTemp->getDiDongLienLacCongDan() . ' : ' . json_encode($notifyResult), 0);
                    }
                    else {
                        $notifyResult = $zalo->sendInviteMessage();
                        $this->INSERT_LOG('Gửi invite zalo đến hồ sơ: ' . $hoSoTemp->getSoHoSo() . ' SĐT: ' . $hoSoTemp->getDiDongLienLacCongDan() . ' : ' . json_encode($notifyResult), 0);
                    }
                }
            }

            $cauHinhZaloNopOnline = (new \Oracle\StoreProcedure\SELECT_CONTENT_ZALO_QTTT([
                'P_MA_QTTT' => $hoSoOnline->getMaQTTT(),
                'P_TYPE' => 9 //Nộp hồ sơ online gửi thông báo cho cán bộ
            ]))->getDefaultResult(0);
            if(!empty($cauHinhZaloNopOnline['CONTENT_ZALO']) && !empty($cauHinhZaloNopOnline['DI_DONG_ZALO'])){
                $dsphone = explode(',', $cauHinhZaloNopOnline['DI_DONG_ZALO']);
                foreach ($dsphone as $value) {
                    if(!empty($value)){
                    $zalo = new Entity\Zalo\ZaloNotificationV2();
                    $zalo->loadConfig($hoSoOnline->getMaCoQuan());
                    $zalo->setHoSo($hoSoOnline);
                    $zalo->setPhone($value);
                    if($zalo->checkUserId()){

                        $noiDungZalo = $hoSoOnline->replaceVariableHS($cauHinhZaloNopOnline['CONTENT_ZALO']);
                        $zalo->setMessageMode(Entity\Zalo\ZaloNotificationV2::MESSAGE_MODE_APPLIED);
                        $zalo->setMessageBody($noiDungZalo);
                        $logZalo = new \Oracle\OracleFunction\INSERT_LOG_THONG_BAO(array(
                            'P_MA_HO_SO_ONLINE' => $hoSoOnline->getMaHoSo() ?: '',
                            'P_MA_CAN_BO_THUC_HIEN' => '',
                            'P_MA_THAO_TAC' => 6,
                            'P_NOI_DUNG' => 'Gửi zalo cho cán bộ khi người dân nộp hồ sơ '.$noiDungZalo ?: ''
                        ));
                        $notifyResult = $zalo->sendEDocStatusMessage(null, 1);
                        Model\System\Logger::add('Gửi zalo đến hồ sơ: ' . $hoSoOnline->getSoHoSo() . ' SĐT: ' . $hoSoOnline->getDiDongLienLacCongDan() . ' : ' . json_encode($notifyResult));
                    }else{
                        $notifyResult = $zalo->sendInviteMessage();
                    }
                    }
                }
            }
            }

            if(!empty($cauHinhZaloNopOnline['DI_DONG_SMS']) && !empty($cauHinhZaloNopOnline['CONTENT_SMS'])){
                $dsphone = explode(',', $cauHinhZaloNopOnline['DI_DONG_SMS']);
                foreach ($dsphone as $value) {
                    if(!empty($value)){
                        $noiDungSMS = $hoSoOnline->replaceSMS($cauHinhZaloNopOnline['CONTENT_SMS']);
                    $sms = new SMS\SMS();
                    $sms->setMobile($value);
                    $sms->setContent($noiDungSMS);
                    $brandName = new SMS\Brandname($sms);
                    $brandName->setMaCoQuan($hoSoOnline->getMaCoQuan());
                    $rs = $brandName->send();
                    }
                }
            }

            if(!empty($cauHinhZaloNopOnline['CONTENT_MAIL']) &&  !empty($cauHinhZaloNopOnline['MAIL']) ){
                $dsmail = explode(',', $cauHinhZaloNopOnline['MAIL']);
                foreach ($dsmail as $value) {
                    if(!empty($value)){
                        $noiDungMAIL = $hoSoOnline->replaceVariableHS($cauHinhZaloNopOnline['CONTENT_MAIL']);
                        $this->GUI_EMAIL_QUY_TRINH($value, $cauHinhZaloNopOnline['TITLE_MAIL'], $noiDungMAIL, '', true);
                    }
                }
            }

//
//            //cập nhật lại mã hồ sơ tại hs_du_lieu_ho_so Nhi  $hoSoOnline->getMaHoSo()
//            $kq = (new Oracle\Package\BCA_DICH_VU_CONG())->INSERT_HS_DLHS_ONL_BCA([
//                'P_MA_HO_SO_ONLINE'=> $hoSoOnline->getMaHoSo(),
//                'P_SID' => $data['sid'],
//                'P_GIA_TRI' => '{"oldver":1,"name":null,"value":null}'
//            ]);
//            $kq = new \Oracle\StoreProcedure\INSERT_HS_DLHS_ONL_N([
//                                            'P_MA_HO_SO_ONLINE'=> $hoSoOnline->getMaHoSo(),
//                                            'P_SID' => $data['sid'],
//                                            'P_GIA_TRI' => '{"oldver":1,"name":null,"value":null}'
//                                        ]);
            $request = $this->getRequest();
            $link = $request->getUri()->getScheme() . '://' . $request->getUri()->getHost() . $this->goback_url;
            $maMucDo = $hoSoOnline->getThuTuc()->getMucDo()->getMaMucDo();
            $maCoQuan = $hoSoOnline->getMaCoQuan();
            $this->call('emailcanhbao');
            if(false){
                if ($maMucDo == 'MUC_DO_3' || $maMucDo == 'MUC_DO_4') {
                    $this->emailcanhbao->goiEmailCanhBao($maCoQuan, 1,
                    $link . sprintf('%sbo-cong-an/tiep-nhan-online/da-nop-ho-so?sid=%s',
                    SITE_ROOT, $data['sid']));
                }
            }
            //end goi mail canh bao
            $applier->sessionSet(Applier::ET_HO_SO_ONLINE, $hoSoOnline);
            if (!empty($responseVietinbank)) {
                header(sprintf('Location: %sbo-cong-an/tiep-nhan-online/da-nop-ho-so?sid=%s&responseVietinbank=%s', SITE_ROOT, $data['sid'], $responseVietinbank));
                exit;
            } else {
                header(sprintf('Location: %sbo-cong-an/tiep-nhan-online/da-nop-ho-so?sid=%s', SITE_ROOT, $data['sid']));
                exit;
            }
        }
        $conn->rollback();
        $applier->sessionSet(Applier::ER_MESSAGE, $resultInfo->getMessage());
        header(sprintf('Location: %sbo-cong-an/tiep-nhan-online/thong-bao-loi?sid=%s', SITE_ROOT, $data['sid']));
        exit;
    }

    public function thanhToanVietinBank ($hoSoOnline) {

        $taiKhoanCongDan = new \Model\CongDan();
        $duLieuCongDan = $taiKhoanCongDan->getSessionData();
        $taiKhoanNganHang = $this->SELECT_TAI_KHOAN_NGAN_HANG(!empty($duLieuCongDan['P_MA_CONG_DAN']) ? $duLieuCongDan['P_MA_CONG_DAN'] : 0);

        if (count($taiKhoanNganHang) > 0) {

            if ($taiKhoanNganHang['STATUS'] == 2) {
                $milliseconds = round(microtime(true) * 1000);
                $YmdHis = date('YmdHis');

                $header = new \Model\Entity\VietinBank\CommonHeader();
                $header->setMsgId($milliseconds);
                $header->setMsgType('1003');
                $header->setSenderCode('SO_TTTT_LAN');
                $header->setSenderName('Dịch vụ công tỉnh Long An');
                $header->setReceiveCode('VietinBank');
                $header->setReceiveName('Ngân hàng VietinBank Long An');
                $header->setCreatedDate($YmdHis);

                $body = new \Model\Entity\VietinBank\CommonBody();
                $body->setTranId($milliseconds);
                $body->setTranDate($YmdHis);
                $body->setTaxCode(!empty($taiKhoanNganHang['TAX_CODE']) ? $taiKhoanNganHang['TAX_CODE'] : '');
                $body->setProfilecode($hoSoOnline->getSoHoSo());
                $body->setOfficeCode($hoSoOnline->getCoQuan()->getMaCoQuan());
                $body->setOfficeName($hoSoOnline->getCoQuan()->getTenCoQuan());
                $body->setBankCode(!empty($taiKhoanNganHang['MA_NGAN_HANG']) ? $taiKhoanNganHang['MA_NGAN_HANG'] : '');
                $body->setBankName(!empty($taiKhoanNganHang['TEN_NGAN_HANG']) ? $taiKhoanNganHang['TEN_NGAN_HANG'] : '');
                $body->setAccNumber(!empty($taiKhoanNganHang['ACC_NUMBER']) ? $taiKhoanNganHang['ACC_NUMBER'] : '');
                $body->setAccName(!empty($taiKhoanNganHang['ACC_NAME']) ? $taiKhoanNganHang['ACC_NAME'] : '');
                $body->setAmount($hoSoOnline->getDmLePhiHoSo()->tinhLePhiChuaThanhToan(['thanhToanCho' => 1]));
                $body->setFee(0);
                $body->setVat(0);
                $body->setCurrency('VND');

                $arrHeader = $header->toArrayHeader();
                $arrBody = $body->toArray1003();

                $xmlMessage = \Model\VietinBank\XmlSerializer::generateValidXmlFromObj($arrBody, $arrHeader);
                $xmlMessage = preg_replace('/(\>)\s*(\<)/m', '$1$2', $xmlMessage);

                $sign = $this->sign($xmlMessage);

                if ($sign['status'] == 'success') {

                    $messageLog = Model\Entity\VietinBank\VietinBankMessageLog::fromProperties([
                        'msgId'        => $header->getMsgId(),
                        'packageType'  => $header->getMsgType(),
                        'senderCode'   => $header->getSenderCode(),
                        'receiverCode' => $header->getReceiveCode(),
                        'createDate'   => $header->getCreatedDate(),
                        'transId'      => $body->getTranId(),
                        'transDate'    => $body->getTranDate(),
                        'content'      => $sign['xml'],
                        'maHoSo'       => $hoSoOnline->getMaHoSo(),
                        'maCongDan'    => $duLieuCongDan['P_MA_CONG_DAN'],
                        'msgType'      => 0,
                        'msgStatus'    => 0,
                    ]);
                    $messageLog->update();

                    $url = Model\Entity\System\Parameter::fromId('VIETINBANK_URL_REQUEST')->getValue();

                    if (!empty($url)) {

                        $xmlRequest = preg_replace('/(\>)\s*(\<)/m', '$1$2', $sign['xml']);
                        $xmlRequest = trim($xmlRequest);
                        $input = "<![CDATA[$xmlRequest]]>";
                        $input = preg_replace('/(\>)\s*(\<)/m', '$1$2', $input);

                        $soapRequest  = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
                        $soapRequest .= "<soap:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\">\n";
                        $soapRequest .= "  <soap:Body>\n";
                        $soapRequest .= "    <BankProcess xmlns=\"http://tempuri.org/\">\n";
                        $soapRequest .= "      <xmlmsg>$input</xmlmsg>\n";
                        $soapRequest .= "    </BankProcess>\n";
                        $soapRequest .= "  </soap:Body>\n";
                        $soapRequest .= "</soap:Envelope>";

                        $headerRequest = array(
                            "Content-type: text/xml;charset=\"utf-8\"",
                            "Accept: text/xml",
                            "Cache-Control: no-cache",
                            "Pragma: no-cache",
                            "SOAPAction: \"http://tempuri.org/BankProcess\"",
                            "Content-length: " . strlen($soapRequest),
                        );

                        $soap_do = curl_init();
                        curl_setopt($soap_do, CURLOPT_URL, $url);
                        curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 10);
                        curl_setopt($soap_do, CURLOPT_TIMEOUT,        10);
                        curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true );
                        curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
                        curl_setopt($soap_do, CURLOPT_POST,           true );
                        curl_setopt($soap_do, CURLOPT_POSTFIELDS,     $soapRequest);
                        curl_setopt($soap_do, CURLOPT_HTTPHEADER,     $headerRequest);

                        $response = curl_exec($soap_do);

                        if($response == false) {

                            $err = 'Curl error: ' . curl_error($soap_do);
                            curl_close($soap_do);
                            exit('Thanh toán Vietinbank thất bại, Không thể gửi yêu cầu đến VietinBank ' . $err);

                        } else {
                            curl_close($soap_do);
                            // $hoSoOnline->updateTrangThaiHoSo('DANG_CAP_NHAT');
                            $messageLog->updateStatusMessageLog(2);

                            $response1 = str_replace("<soap:Body>","",$response);
                            $response2 = str_replace("</soap:Body>","",$response1);
                            $xml=simplexml_load_string($response2);

                            $verify = $this->checkVerify((string)$xml->BankProcessResponse->BankProcessResult);

                            if ($verify['status'] == 'error') {
                                exit('Thanh toán Vietinbank thất bại, ' . $verify['message']);

                            } elseif ($verify['status'] == 1) {

                                $xmlData=simplexml_load_string((string)$xml->BankProcessResponse->BankProcessResult);
                                $msgType = (string)$xmlData->Header->MSG_TYPE;

                                $logResponse = new Model\Entity\VietinBank\VietinBankMessageLog();
                                $logResponse->setMsgId((string)$xmlData->Header->MSG_ID);
                                $logResponse->setMsgRefId($messageLog->getMsgId());
                                $logResponse->setPackageType($msgType);
                                $logResponse->setSenderCode((string)$xmlData->Header->SENDER_CODE);
                                $logResponse->setReceiverCode((string)$xmlData->Header->RECEIVER_CODE);
                                $logResponse->setCreateDate((string)$xmlData->Header->CREATED_DATE);
                                $logResponse->setTransId((string)$xmlData->Data->TRANS_ID);
                                $logResponse->setTransRefId($messageLog->getTransId());
                                $logResponse->setTransDate((string)$xmlData->Data->TRANS_DATE);
                                $logResponse->setContent((string)$xml->BankProcessResponse->BankProcessResult);
                                $logResponse->setMaCongDan($duLieuCongDan['P_MA_CONG_DAN']);
                                $logResponse->setMsgType(1);
                                $logResponse->setMsgStatus(0);
                                $logResponse->update();

                                if ($msgType == '1099') {
                                    $transRefId = (string)$xmlData->Data->TRANS_REF_ID;

                                    if ($transRefId == $body->getTranId()) {
                                        $errorCode = (string)$xmlData->Data->ERROR_CODE;
                                        $errorDesc = (string)$xmlData->Data->ERROR_DESC;
                                        if ($errorCode == '00') {
                                            return json_encode(['status' => 'success', 'message' => 'Đã gửi thông tin đăng ký tài khoản tới VietinBank!']);
                                        } else {
                                            return json_encode(['status' => 'error', 'message' => $errorCode . ': ' .$errorDesc]);
                                        }
                                    } else {
                                        return json_encode(['status' => 'error', 'message' => 'Vietinbank phản hồi không đúng giao dich [TRANS_REF_ID]']);
                                    }
                                }
                                if ($msgType == '2003') {

                                    $transRefId = (string)$xmlData->Data->TRANS_REF_ID;
                                    if ($transRefId == $body->getTranId()) {

                                        $accNumber = (string)$xmlData->Data->ACC_NUMBER;
                                        $accName = (string)$xmlData->Data->ACC_NAME;
                                        $confirmStatus = (string)$xmlData->Data->CONFIRM_STATUS;
                                        $confirmDesc = (string)$xmlData->Data->CONFIRM_DESC;
                                        if ($confirmStatus == 'Y') {
                                            return json_encode(['status' => 'success', 'message' => 'Đã gửi thông tin đăng ký tài khoản tới VietinBank!']);
                                        } else {
                                            return json_encode(['status' => 'error', 'message' => $confirmStatus . ': ' .$confirmDesc]);
                                        }
                                    } else {
                                        return json_encode(['status' => 'error', 'message' => 'Vietinbank phản hồi không đúng giao dich [TRANS_REF_ID]']);
                                    }
                                }

                            } else {
                                return json_encode(['status' => 'error', 'message' => 'Xác nhận ký số từ VietinBank thất bại!']);
                            }
                        }



                    } else {
                        exit('Thanh toán Vietinbank thất bại, Chưa cấu hình tham số hệ thống VIETINBANK_URL_REQUEST cho link webserviece! ');
                    }

                } else {
                    exit('Thanh toán Vietinbank thất bại, lỗi ' . $sign['message']);
                }

            } else {
                exit('Tài khoản công dân chưa được xác nhận!');
            }

        } else {
            exit('Tài khoản công dân chưa có đăng ký tài khoản ngân hàng!');
        }

    }

    public function nopLePhiTrucTuyen() {
        $conn = Connection::getConnection();
        $conn->turnOffAutoCommit();
        $hoSo = Entity\HoSo::fromPost(['source_type' => Entity\HoSo::JSON_PROPERTIES ]);

        if ($hoSo->updateLePhiHoSo() && $hoSo->updateHinhThucThanhToan()) {
            $conn->commit();
            $conn->turnOnAutoCommit();
            $hoSoThanhToan = Entity\HoSo::fromMaHoSo((int) $hoSo->getMaHoSo());
            $hoSoThanhToan->laore();
            $countDaThanhToan = 0;
            $countChuaThanhToan = 0;
            $phiChuaThanhToan = 0;
            foreach ($hoSoThanhToan->getDmLePhiHoSo()->getItems() as $value) {
                if ($value->getSoLuong() === '' or $value->getSoLuong() === null or $value->getSoLuong() == 0 ) {
                    exit('Số lượng của lệ phí phải lớn hơn hoặc bằng 1!');
                }
                if ($value->getDaThanhToan() == 1) {
                    $countDaThanhToan++;
                }

                if ($value->getBatBuocThanhToan() == 1 && $value->getDaThanhToan() == 0) {
                    $phiChuaThanhToan = $phiChuaThanhToan + ($value->getMucLePhi() * $value->getSoLuong());
                    $countChuaThanhToan++;
                }
            }
            $applier = new Applier($hoSoThanhToan->getMaHoSo());
            if ($hoSoThanhToan->phaiThanhToanTrucTuyen()){

                if ($phiChuaThanhToan <= 0) {
                    exit('Số tiền nhỏ hơn hoặc bằng 0, không thể thanh toán trực tuyến!');
                }

                if ($hoSoThanhToan->phaiThanhToanQuaVNAY()) {
                    if ($phiChuaThanhToan >= Entity\HinhThucThanhToan::fromMaHinhThuc(8)->getPhiThanhToan()) {
                        //$applier = new Applier($hoSoThanhToan->getMaHoSoOnline());
                        $vnPayInitUrlRequest = Entity\VNPay\InitUrlRequest::fromHoSo($hoSoThanhToan);
                        $vnPayInitUrlRequest->createUrl();
                        if ($vnPayInitUrlRequest->update() && $vnPayInitUrlRequest->updateThanhToanVNPay()) {
                            // $hoSoThanhToan->setVNPayInitRequest($vnPayInitUrlRequest);
                            // $applier->sessionSet(Applier::ET_HO_SO_ONLINE, $hoSoThanhToan);
                            $applier->setId($vnPayInitUrlRequest->getTxnRef());
                            $applier->sessionSet(Applier::ET_HO_SO_ONLINE, $hoSoThanhToan);
                            $applier->sessionSet(Applier::KHOA_CAP_NHAT, $hoSoThanhToan->getKhoaCapNhat());
                            header(sprintf('Location: %s', $vnPayInitUrlRequest->getUrlRequest()));
                            exit;
                        }
                        exit('Some errors occurred, VNPay can not serve!');
                    } else {
                        exit('Lệ phí chưa đủ để thanh toán qua VNPay!');
                    }
                }

                if ($hoSoThanhToan->phaiThanhToanQuaVNPTPay()) {
                    if ($phiChuaThanhToan >= Entity\HinhThucThanhToan::fromMaHinhThuc(7)->getPhiThanhToan()){
                        $vnptPayInitRequest = Entity\VNPTPay\InitRequest::fromHoSo($hoSoThanhToan);
                        if ($vnptPayInitRequest->update() && $vnptPayInitRequest->send()) {
                            $hoSoThanhToan->updateVNPTPayInitRequestId($vnptPayInitRequest->getMerchantOrderId());
                            $hoSoThanhToan->setVNPTPayInitRequest($vnptPayInitRequest);
                            $applier->sessionSet(Applier::ET_HO_SO_ONLINE, $hoSoThanhToan);
                            $applier->setId($vnptPayInitRequest->getMerchantOrderId());
                            $applier->sessionSet(Applier::ET_HO_SO_ONLINE, $hoSoThanhToan);
                            $applier->sessionSet(Applier::KHOA_CAP_NHAT, $hoSoThanhToan->getKhoaCapNhat());
                            header(sprintf('Location: %s', $vnptPayInitRequest->getInitResponse()->getRedirectURL()));
                            exit;
                        }
                        exit('Some errors occurred, VNPT Pay can not serve!');
                    } else {
                        exit('Lệ phí chưa đủ để thanh toán qua VNPT Pay!');
                    }
                }

                //if ($hoSoThanhToan->phaiThanhToanQuaPaymentPlatform()) {
                $check_thanhtoan_PP_TanDan =Entity\System\Parameter::fromId('PAYMENT_PLATFORM_TAN_DAN_ACTIVE')->getValue();
                if ($hoSoThanhToan->phaiThanhToanQuaPaymentPlatform()) {
                    if ($phiChuaThanhToan >= Entity\HinhThucThanhToan::fromMaHinhThuc(10)->getPhiThanhToan()){
                        if ($check_thanhtoan_PP_TanDan==1) {
                            $paymentPlatform = Entity\PaymentPlatform\InitRequestPaymentPlatformTanDan::fromHoSo($hoSoThanhToan);
                        }
                        else{
                             $paymentPlatform = Entity\PaymentPlatform\InitRequestPaymentPlatform::fromHoSo($hoSoThanhToan);
                        }
                        if ($paymentPlatform->update() && $paymentPlatform->send()) {

                            $applier->setId($paymentPlatform->getMaThamChieu());
                            $applier->sessionSet(Applier::ET_HO_SO_ONLINE, $hoSoThanhToan);
                            $applier->sessionSet(Applier::KHOA_CAP_NHAT, $hoSoThanhToan->getKhoaCapNhat());
                            header(sprintf('Location: %s', $paymentPlatform->getInitResponse()->getUrlThanhToan()));
                            exit;
                        }

                        exit('Some errors occurred, Payment Platform can not serve!');
                    } else {
                        exit('Lệ phí chưa đủ để thanh toán qua Payment Platform!');
                    }
                }

                if ($hoSoThanhToan->phaiThanhToanQuaBIDV()) {
                    if ($phiChuaThanhToan >= Entity\HinhThucThanhToan::fromMaHinhThuc(11)->getPhiThanhToan()){
                        $BIDVInitRequest = Entity\BIDV\InitRequest::fromHoSo($hoSoThanhToan);
                         if ($BIDVInitRequest->update() && $BIDVInitRequest->send()) {
                            $BIDVInitRequest->updateThanhToanBIDV();
                            //$hoSoOnline->setBIDVInitRequestId($BIDVInitRequest);
                            //$applier->sessionSet(Applier::ET_HO_SO_ONLINE, $hoSoOnline);
                            $applier->setId($BIDVInitRequest->getTransId());
                            $applier->sessionSet(Applier::ET_HO_SO_ONLINE, $hoSoThanhToan);
                            $applier->sessionSet(Applier::KHOA_CAP_NHAT, $hoSoThanhToan->getKhoaCapNhat());
                            header(sprintf('Location: %s', $BIDVInitRequest->getInitResponse()->getRedirectURL()));
                            exit;
                         }
                        exit('Some errors occurred, BIDV can not serve!');
                    } else {
                        exit('Lệ phí chưa đủ để thanh toán qua BIDV!');
                    }
                }

                if ($hoSoThanhToan->phaiThanhToanPayGov()) {
                    if ($phiChuaThanhToan >= Entity\HinhThucThanhToan::fromMaHinhThuc(14)->getPhiThanhToan()){
                        $initRequestPayGov = Entity\PayGov\InitRequestPayGov::fromHoSo($hoSoThanhToan);

                        if ($initRequestPayGov->update() && $initRequestPayGov->send()) {
                            $applier->setId($initRequestPayGov->getOrderId());
                            $applier->sessionSet(Applier::ET_HO_SO_ONLINE, $hoSoThanhToan);
                            $applier->sessionSet(Applier::KHOA_CAP_NHAT, $hoSoThanhToan->getKhoaCapNhat());
                            if ($initRequestPayGov->getInitResponse()->getErrorCode() == 'SUCCESSFUL') {
                                header(sprintf('Location: %s', $initRequestPayGov->getInitResponse()->getUrl()));
                                exit;
                            } else {
                                exit(sprintf('Error code: %s, Error message: %s', $initRequestPayGov->getInitResponse()->getErrorCode(), $initRequestPayGov->getInitResponse()->getErrorMessage()));
                            }
                        }
                        exit('Some errors occurred, PayGov can not serve!');
                    } else {
                        exit('Lệ phí chưa đủ để thanh toán qua PayGov!');
                    }
                }
            }
        } else {
            $conn->rollback();
            exit('Cập nhật lệ phí thất bại!');
        }
    }

    public function ketQuaGiaoDichSmartGate() {
        $queryData = $this->getQueryData();
        $this->checkUpdateRequirement($queryData['sid']);
        $applier = new Applier($queryData['sid']);
        $hoSoOnline = $applier->sessionGet(Applier::ET_HO_SO_ONLINE);
        $smartGatePaymentRequest = $hoSoOnline->getSmartGatePaymentRequest();
        $smartGatePaymentResponse = Entity\SmartGate\PaymentResponse::fromQuery();
        $smartGatePaymentResponse->setPaymentRequest($smartGatePaymentRequest);
        $checksum = $this->getFilter()->filter($this->getRequest()->getQuery('checksum'));
        if ($smartGatePaymentResponse->isValid($checksum) && $smartGatePaymentResponse->update()) {
            $smartGatePaymentRequest->setPaymentResponse($smartGatePaymentResponse);
            if (!$smartGatePaymentRequest->thanhToanThanhCong()) {
                header(sprintf('Location: %sbo-cong-an/tiep-nhan-online/thanh-toan-le-phi-that-bai?sid=%s&token=%s', SITE_ROOT, $queryData['sid'], (new Csrf('tttb'))->getHash()));
                exit;
            }
            $hoSoOnline->getDmLePhiHoSo()->updateDaThanhToan(1, ['thanhToanCho' => 1, 'batBuocThanhToan' => 1, 'fromDatabase' => true]);
            $hoSoOnline->updateTrangThaiHoSo('DA_NOP');
            $hoSoOnline->guiThongBaoDaNop();
            $applier->sessionSet(Applier::ET_HO_SO_ONLINE, $hoSoOnline);
            header(sprintf('Location: %sbo-cong-an/tiep-nhan-online/da-nop-ho-so?sid=%s', SITE_ROOT, $queryData['sid']));
            exit;
        }
        exit('Some errors occurred, Payment infomation can not save!!!');
    }

    public function huyGiaoDichSmartGate() {
        $queryData = $this->getQueryData();
        $this->checkUpdateRequirement($queryData['sid']);
        header(sprintf('Location: %sbo-cong-an/tiep-nhan-online/xac-nhan-thong-tin-nop?sid=%s', SITE_ROOT, $queryData['sid']));
        exit;
    }

    public function vnptpayPaymentSuccess() {
        $queryData = $this->getQueryData();
        $this->checkUpdateRequirement($queryData['sid']);
        $applier = new Applier($queryData['sid']);
        $hoSoOnline = $applier->sessionGet(Applier::ET_HO_SO_ONLINE);
        $initRequest = $hoSoOnline->getVNPTPayInitRequest();
        $initRequest->lre(['VNPTPayConfirmRequest', 'VNPTPayConfirmResponse']);
        if ($initRequest->paymentSuccess()) {
            $hoSoOnline->getDmLePhiHoSo()->updateDaThanhToan(1, ['thanhToanCho' => 1, 'batBuocThanhToan' => 1, 'maHoSoOnline' => $hoSoOnline->getMaHoSo(), 'fromDatabase' => true]);
            $hoSoOnline->updateTrangThaiHoSo('DA_NOP');
            // $hoSoOnline->guiThongBaoDaNop();
            $applier->sessionSet(Applier::ET_HO_SO_ONLINE, $hoSoOnline);
            header(sprintf('Location: %sbo-cong-an/tiep-nhan-online/da-nop-ho-so?sid=%s', SITE_ROOT, $queryData['sid']));
            exit;
        }
        exit('Some errors occurred, VNPT Pay payment has failed!!!');
    }

    //vqhuy.lan - PayGov
    public function getQueryDataPayGov()
    {
        //System\Logger::add('Cổng payGov', ['title' => $this->getRequest()]);
        $request = str_replace('?paygate', '&paygate', $this->getRequest());
        parse_str(parse_url($request, PHP_URL_QUERY), $queries);
        //$filter = $this->getFilter();
        return [
            'sid'           => $queries['sid'],
            'docCode'       => substr($queries['orderId'], 0, strlen($queries['orderId']) - 3),
            'paygate'       => $queries['paygate'],
            'requestCode'   => $queries['requestCode'],
            'orderId'       => $queries['orderId'],
            'orderPayId'    => $queries['orderPayId'],
            'amount'        => $queries['amount'],
            'payDate'       => $queries['payDate'],
            'orderInfo'     => $queries['orderInfo'],
            'payTransId'    => $queries['payTransId'],
            'transactionNo' => $queries['transactionNo'],
            'errorCode'     => $queries['errorCode'],
            'checksum'      => substr($queries['checksum'], 0, 64),
        ];
    }
    public function payGovSuccess()
    {
        $queryData = $this->getQueryDataPayGov();
        $this->checkUpdateRequirement($queryData['sid']);
        $applier = new Applier($queryData['sid']);
        $hoSoOnline = $applier->sessionGet(Applier::ET_HO_SO_ONLINE);
        //$initRequest = $hoSoOnline->getVNPTPayInitRequest();
        //$initRequest->lre(['VNPTPayConfirmRequest', 'VNPTPayConfirmResponse']);
        if ($queryData['errorCode'] == '00') {
            $hoSoOnline->getDmLePhiHoSo()->updateDaThanhToan(1, ['thanhToanCho' => 1, 'batBuocThanhToan' => 1, 'maHoSoOnline' => $hoSoOnline->getMaHoSo(), 'fromDatabase' => true]);
            $hoSoOnline->updateTrangThaiHoSo('DA_NOP');
            //$hoSoOnline->guiThongBaoDaNop();
            $result = $this->updateKetQuaThanhToanPaygov($queryData['docCode'], $queryData['paygate'], $queryData['transactionNo'], $queryData['requestCode'], $queryData['orderId'], $queryData['amount'], $queryData['orderInfo'], $queryData['payDate'], $queryData['errorCode'], 'pay', $queryData['checksum']);
            $applier->sessionSet(Applier::ET_HO_SO_ONLINE, $hoSoOnline);
            header(sprintf('Location: %sbo-cong-an/tiep-nhan-online/da-nop-ho-so?sid=%s', SITE_ROOT, $queryData['sid']));
            exit;
        }
        exit('Some errors occurred, PayGov has failed!!!');
    }
    public function updateKetQuaThanhToanPaygov($soHoSo, $paygate, $transactionNo, $requestCode, $orderId, $amount, $orderInfo, $payDate, $errorCode, $type, $checksum)
    {
        $result = 0;
        $conn = Connection::getConnection();
        $conn->turnOffAutoCommit();
        $result = new OracleFunction\EDIT_PAY_GOV_KET_QUA([
            'P_SO_HO_SO'                => $soHoSo,
            'P_CONG_THANH_TOAN'         => $paygate,
            'P_MA_GIAO_DICH'            => $transactionNo,
            'P_MA_YEU_CAU_THANH_TOAN'   => $requestCode,
            'P_MA_HOA_DON'              => $orderId,
            'P_SO_TIEN'                 => $amount,
            'P_THONG_TIN_HOA_DON'       => $orderInfo,
            'P_THOI_GIAN_THANH_TOAN'    => $payDate,
            'P_MA_LOI'                  => $errorCode,
            'P_LOAI'                    => $type,
            'P_MA_XAC_THUC'             => $checksum,
        ]);
        if ($result->getResult() == 1) {
            $conn->commit();
            $result = 1;
        } else {
            $conn->commit();
            $result = 0;
        }
        // $conn->commit();
        $conn->turnOnAutoCommit();

        return $result;
    }
    //end vqhuy.lan

    public function kiemTraMaNopHoSo() {
        $data = $this->getPostData();
        if (Auth\OTP::isValid($data['code'])) {
            Auth\OTP::free();
            header(sprintf('Location: %s', $data['returnUrl']));
        } else {
            $this->setMessage(new Alert('Thông báo!', 'Mã nộp hồ sơ không đúng', Alert::DANGER), true);
            header(sprintf('Location: %sbo-cong-an/tiep-nhan-online/xac-nhan-ma-nop-ho-so?%s', SITE_ROOT, http_build_query([
                'return-url' => $data['returnUrl']
            ])));
        }
        exit;
    }

    public function guiLaiMaNopHoSo() {
        $data = (new Applier($this->getPostData()['sid']))->sessionGet(Applier::ET_HO_SO_ONLINE);
        $congDan = $data->getCongDan();
        $maCoQuan = $data->getMaCoQuan();
        $SMS_XAC_THUC = (int) Entity\System\Parameter::fromId('DVC_SMS_XAC_THUC_NGUOI_NOP')->getValue();
        $EMAIL_XAC_THUC = (int) Entity\System\Parameter::fromId('DVC_EMAIL_XAC_THUC_NGUOI_NOP')->getValue();
        if ($SMS_XAC_THUC || $EMAIL_XAC_THUC) {
            $url = sprintf('Location: %s', $this->getPostData()['currentUrl']);
            $this->goiMaNopHoSo($congDan, $SMS_XAC_THUC, $EMAIL_XAC_THUC, $url, $maCoQuan?:null);
        }
        exit('INVALID REQUEST');
    }

    public function checkUpdateRequirement($sid) {
        if (($hoSoOnline = (new Applier($sid))->sessionGet(Applier::ET_HO_SO_ONLINE)) && (!($maCongDan = $hoSoOnline->getCongDan()->getMaCongDan()) || (int) $maCongDan === (int) Entity\CongDan::fromSession()->getMaCongDan())) {
            if (!$hoSoOnline->duocPhepCapNhat()) {
                exit('Some errors occurred, Permission denied!');
            }
            if ($hoSoOnline->getTrangThaiHoSo() !== 'DANG_CAP_NHAT') {
                $hoSoOnline->updateTrangThaiHoSo('DANG_CAP_NHAT');
            }
        } else {
            if ($this->getRequest()->isGet()) {
                header(sprintf('Location: %sbo-cong-an/tiep-nhan-online/xac-minh-ho-so?%s', SITE_ROOT, http_build_query([
                    'sid' => $sid,
                    'return-url' => $this->getRequest()->getRequestUri()
                ])));
                exit;
            }
            exit('Permission denied');
        }
    }

    public function checkSessionHoSo($sid) {
        if (!($hoSoOnline = (new Applier($sid))->sessionGet(Applier::ET_HO_SO_ONLINE)) || (int) $hoSoOnline->getCongDan()->getMaCongDan() !== (int) Entity\CongDan::fromSession()->getMaCongDan()) {
            if ($this->getRequest()->isGet()) {
                header(sprintf('Location: %sbo-cong-an/tiep-nhan-online/xac-minh-ho-so?%s', SITE_ROOT, http_build_query([
                    'sid' => $sid,
                    'return-url' => $this->getRequest()->getRequestUri()
                ])));
                exit;
            }
            exit('Permission denied');
        }
    }

    public function checkViewRequirement($sid) {
        if (!($hoSoOnline = (new Applier($sid))->sessionGet(Applier::ET_HO_SO_ONLINE))) {

                if ($this->getRequest()->isGet()) {
                    header(sprintf('Location: %sbo-cong-an/tiep-nhan-online/xac-minh-ho-so?%s', SITE_ROOT, http_build_query([
                        'sid' => $sid,
                        'return-url' => $this->getRequest()->getRequestUri()
                    ])));
                    exit;
                }
                exit('Permission denied');

        }

        $sessionMaCongDan = Entity\CongDan::fromSession()->getMaCongDan();

        if (!empty($sessionMaCongDan) && $sessionMaCongDan != null && $sessionMaCongDan != '') {

            if ((int) $hoSoOnline->getCongDan()->getMaCongDan() !== (int) Entity\CongDan::fromSession()->getMaCongDan() && !(new Applier($sid))->sessionGet(Applier::KHOA_CAP_NHAT)) {

                if ($this->getRequest()->isGet()) {
                    header(sprintf('Location: %sbo-cong-an/tiep-nhan-online/xac-minh-ho-so?%s', SITE_ROOT, http_build_query([
                        'sid' => $sid,
                        'return-url' => $this->getRequest()->getRequestUri()
                    ])));
                    exit;
                }
                exit('Permission denied');
            }

        } else {
            if (!(new Applier($sid))->sessionGet(Applier::KHOA_CAP_NHAT)) {

                if ($this->getRequest()->isGet()) {
                    header(sprintf('Location: %sbo-cong-an/tiep-nhan-online/xac-minh-ho-so?%s', SITE_ROOT, http_build_query([
                        'sid' => $sid,
                        'return-url' => $this->getRequest()->getRequestUri()
                    ])));
                    exit;
                }
                exit('Permission denied');
            }
        }

    }

    public function napDuLieuCapNhat() {
        $queryData = $this->getQueryData();
        $hoSoOnline = Entity\HoSoOnline::fromMaHoSo((int) $queryData['sid']);
        $thanhToanTrucTuyenMucDo23 = Model\Entity\System\Parameter::fromId('THANH_TOAN_TRUC_TUYEN_MUC_DO_2_3')->getValue();
        if (!$hoSoOnline->exists() && $thanhToanTrucTuyenMucDo23 == 1) {
            $hoSoOnline = Entity\HoSo::fromMaHoSo((int) $queryData['sid']);
        }
        if ($hoSoOnline->getMaHoSo()) {
            $applier = new Applier($queryData['sid']);
            if ($maCongDan = (int) $hoSoOnline->getMaCongDan()) {
                if ($maCongDan !== (int) Entity\CongDan::fromSession()->getMaCongDan()) {
                    exit('Permission denied');
                }
            } else {
                if ($hoSoOnline->getKhoaCapNhat() !== $applier->sessionGet(Applier::KHOA_CAP_NHAT)) {
                    exit('Permission denied');
                }
            }
            $hoSoOnline->laore();
            $applier->sessionSet(Applier::ET_HO_SO_ONLINE, $hoSoOnline);
            header(sprintf('Location: %s', $queryData['returnUrl']));
            exit;
        }
        exit('No data found');
    }

    public function kiemTraKhoaCapNhatHoSo() {
        $queryData = $this->getQueryData();
        $postData = $this->getPostData();
        $hoSoOnline = Entity\HoSoOnline::fromMaHoSo((int) $queryData['sid']);
        $thanhToanTrucTuyenMucDo23 = Model\Entity\System\Parameter::fromId('THANH_TOAN_TRUC_TUYEN_MUC_DO_2_3')->getValue();
        if (!$hoSoOnline->exists() && $thanhToanTrucTuyenMucDo23 == 1) {
            $hoSoOnline = Entity\HoSo::fromMaHoSo((int) $queryData['sid']);
        }
        if ($hoSoOnline->getKhoaCapNhat() === $postData['khoaCapNhat']) {
            (new Applier($queryData['sid']))->sessionSet(Applier::KHOA_CAP_NHAT, $postData['khoaCapNhat']);
            header(sprintf('Location: %s', SITE_ROOT . 'bo-cong-an/tiep-nhan-online/nap-du-lieu-cap-nhat?' . http_build_query([
                'sid' => $queryData['sid'],
                'return-url' => $queryData['returnUrl']
            ])));
            exit;
        }
        return new ResultInfo(0, 'Khóa cập nhật chưa đúng!', $postData);
    }

    public function exportTemplate() {
        $isPost = $this->getRequest()->isPost();
        $data = $isPost ? $this->getPostData() : $this->getQueryData();
        $this->checkSessionHoSo($data['sid']);
        $template = Entity\Template::fromId($data['templateId']);
        if ($template->getId()) {
            $applier = new Applier($data['sid']);
            $hoSoOnline = $applier->sessionGet(Applier::ET_HO_SO_ONLINE);
            if ($isPost) {
                $hoSoOnline->merge(Entity\HoSoOnline::fromPost(), function ($prop) {
                    return in_array($prop->name, [
                        'maBieuMau'
                        , 'duLieuBieuMau'
                        , 'ghiChu'
                        , 'giayToKhac'
                    ]);
                });
                $hoSoOnline->setDanhSachGiayToNop(DanhMuc\GiayToCuaHoSoOnline::fromPost(['lre_options' => ['BieuMau', 'Template']]));
            }
            $file = $template->export($hoSoOnline->exportStandardData());
            $file->download()->remove();
            exit;
        }
        exit('Template not found');
    }

    public function makeDSGiayToKhac($maHoSo){
        $dsGiayToKhacPost = $this->getPostData()['dsGiayToKhac'];
        $maHoSoOnline = $this->getPostData()['maHoSo'];
        if ($maHoSoOnline == '' or empty($maHoSoOnline)) {
            $maHoSoOnline = $maHoSo;
        }
        $createDate = date('d/m/Y H:i:s');
        $danhMucGiayToKhac = new DanhMuc\GiayToKhacCuaHoSo([
            'maHoSoOnline' => $maHoSoOnline
        ]);
        foreach($danhMucGiayToKhac->getItems() as $item){
            $item->setIsRemoved(true);
        }
        foreach($dsGiayToKhacPost as $giayToPost){
            if($giayToPost['id']){
                $giayToKhac = $danhMucGiayToKhac->getItem($giayToPost['id']);
                $giayToKhac->setIsRemoved(false);
            }else{
                $giayToKhac = new Entity\GiayToKhacCuaHoSo();
                $giayToKhac->setTenGiayTo($giayToPost['filename']);
                $giayToKhac->setSoBan(1);
            }
            if($giayToPost['filetitle']){
                $newfilepath = System::getDefaultUploader()->setPostname($giayToPost['postname'])->upload();
                $giayToKhac->setFileGiayTo($newfilepath);
            }else{
                $giayToKhac->setFileGiayTo($giayToPost['filepath']);
            }
            $giayToKhac->setMaLoaiGiayToKhac($giayToPost['type']);
            $giayToKhac->setNgayTao($createDate);
            if(!$giayToPost['id']){
                $danhMucGiayToKhac->addItem($giayToKhac);
            }
        }
        return $danhMucGiayToKhac;
    }

    public function getMaVilis($maQttt,$maQuanHuyen){
        $package = new \Oracle\Package\MC_VILIS();

        return $package->GET_MA_VILIS_BY_MA_QTTT([
            'P_MA_QTTT' => $maQttt,
            'P_MA_QUAN_HUYEN' => $maQuanHuyen
        ]);

    }

    public function remakeUrl($url,$maHoSo){
        $sIDPosition = strpos($url, 'sid=');
        if($sIDPosition > 0){
            return sprintf('%s%s',substr($url,0, $sIDPosition+4 ),$maHoSo);
        }
        return $url;
    }

    // Selet table SELECT_DM_DANH_GIA_DVTT
    public function SELECT_DM_DANH_GIA_DVTT() {
        return (new Oracle\StoreProcedure\SELECT_DM_DANH_GIA_DVTT([]))->getDefaultResult();
    }

    public function INSERT_HS_DANH_GIA_DVTT() {
        $filter = $this->getFilter();
        $request = $this->getRequest();
        $MA_HO_SO = $filter->filter($request->getPost('MA_HO_SO'));
        $MA_MUC = $filter->filter($request->getPost('MA_MUC'));
        $Y_KIEN = $filter->filter($request->getPost('Y_KIEN'));

        $result = (new OracleFunction\INSERT_HS_DANH_GIA_DVTT([
            'P_MA_HO_SO_ONLINE' => $MA_HO_SO,
            'P_MA_MUC'          => $MA_MUC,
            'P_Y_KIEN'          => $Y_KIEN
        ]))->getResult();

        if ($result == 'MA_MUC_KHONG_TON_TAI') {
            $this->exec_fail($this->goback_url, 'Mã mục đánh giá không tồn tại!');
            return;
        } else if ($result == 'THAT_BAI') {
            $this->exec_fail($this->goback_url, 'Nhận xét thất bại!');
            return;
        } else if ($result == 'THANH_CONG') {
            $this->exec_done($this->goback_url);
            return;
        } else if ($result == 'HS_DA_DANH_GIA') {
            $this->exec_fail($this->goback_url, 'Hồ sơ này đã có nhận xét!');
            return;
        }
    }

    public function SELECT_CO_QUAN_DON_VI() {
        return (new Oracle\StoreProcedure\SELECT_CO_QUAN_DON_VI([]))->getDefaultResult();
    }

    public function layThongTinNguoiNopTuSession() {
        $queryData = $this->getQueryData();
        $applier = new Applier($queryData['sid']);
        $hoSoOnline = $applier->sessionGet(Applier::ET_HO_SO_ONLINE);
        $hoSoOnline->setCongDan(Entity\CongDan::fromSession());
        $applier->sessionSet(Applier::ET_HO_SO_ONLINE, $hoSoOnline);
        header(sprintf('Location: %sbo-cong-an/tiep-nhan-online/nhap-thong-tin-nguoi-nop-ho-so?sid=%s', SITE_ROOT, $queryData['sid']));
        exit;
    }

    public function goiMaNopHoSo($congDan, $SMS_XAC_THUC, $EMAIL_XAC_THUC, $url, $maCoQuan = null) {
        $MA_XAC_THUC = Auth\OTP::generate();
        $message = '';
        if ($SMS_XAC_THUC && $congDan->getDiDong() != '') {
            $this->GUI_SMS($congDan->getDiDong(), sprintf('Ma xac thuc cua ban la %s', $MA_XAC_THUC), $maCoQuan);
        } else if ($SMS_XAC_THUC && $congDan->getDiDong() == '') {
            $message = 'Không tồn tại số di động &nbsp;&nbsp;';
        }
        if ($EMAIL_XAC_THUC && $congDan->getEmail() != '') {
            $noidung_email = "Mã xác thực của bạn là " . $MA_XAC_THUC;
            $this->GUI_EMAIL($congDan->getEmail(),'IGATE - VNPT', $noidung_email);
        } else if ($EMAIL_XAC_THUC && $congDan->getEmail() == '') {
            $message .= 'Không tồn tại email';
        }
        if ($message != '') {
            $this->setMessage(new Alert('Thông báo!', $message, Alert::DANGER), true);
        }
        header($url);
        exit;
    }
    /**
     * them menu trai
     */
     public function SELECT_MA_CQ_THEO_MA_DM_DV($maDMDV){
        $rows = new Oracle\StoreProcedure\SELECT_MA_CQ_THEO_MA_DM_DV(
            Array('P_MA_DM_DON_VI' => $maDMDV));
        return $rows->getDefaultResult(0)->MA_CO_QUAN;
    }
    public function GET_DS_LINHVUC_COQUAN_ONL($maCQ, $type, $huyen=0, $xa=0) {
        $parameters = Array('P_MA_CO_QUAN'=>$maCQ,'P_TYPE'=>$type, 'P_HUYEN' => $huyen, 'P_XA' => $xa);
        $parameters['FUNCTION_NAME'] = 'GET_DS_LINHVUC_COQUAN_ONL';
        $cacheKey = $this->createCacheName($parameters);
        $cache = $this->createCache('cache/frontend/dichvucong/tiepnhanonline', 8);
        if ($cache->hasItem($cacheKey)) {
            $rows = unserialize($cache->getItem($cacheKey));
        }else{
            $rows = (new Oracle\StoreProcedure\GET_DS_LINHVUC_COQUAN_ONL($parameters))->getDefaultResult();
            $cache->setItem($cacheKey, serialize($rows));
        }

        return $rows;
    }
    // Nhi issue IGATESUPP-7418
    public function GET_DS_LINHVUC_COQUAN_THEO_CAP($maCQ, $type, $onl) {
        $parameters = Array('P_MA_CO_QUAN'=>$maCQ,'P_TYPE'=>$type, 'P_ONL' => $onl, 'P_HIEN_THI_CAP_HX' => 0);
        $parameters['FUNCTION_NAME'] = 'GET_DS_LINHVUC_COQUAN_THEO_CAP';
        $cacheKey = $this->createCacheName($parameters);
        $cache = $this->createCache('cache/frontend/dichvucong/tiepnhanonline', 8);
        if ($cache->hasItem($cacheKey)) {
            $rows = unserialize($cache->getItem($cacheKey));
        }else{
            $rows = (new Oracle\StoreProcedure\GET_DS_LINHVUC_COQUAN_THEO_CAP($parameters))->getDefaultResult();
            $cache->setItem($cacheKey, serialize($rows));
        }

        return $rows;
    }
    public function GET_DS_LINHVUC_COQUAN_CAP_XA($maCQ, $type, $onl) {
        $parameters = Array('P_MA_CO_QUAN'=>$maCQ,'P_TYPE'=>$type, 'P_ONL' => $onl, 'P_HIEN_THI_CAP_HX' => 0);
        $parameters['FUNCTION_NAME'] = 'GET_DS_LINHVUC_COQUAN_CAP_XA';
        $cacheKey = $this->createCacheName($parameters);
        $cache = $this->createCache('cache/frontend/dichvucong/tiepnhanonline', 8);
        if ($cache->hasItem($cacheKey)) {
            $rows = unserialize($cache->getItem($cacheKey));
        }else{
            $rows = (new Oracle\StoreProcedure\GET_DS_LINHVUC_COQUAN_CAP_XA($parameters))->getDefaultResult();
            $cache->setItem($cacheKey, serialize($rows));
        }

        return $rows;
    }
    public function SELECT_DS_THUTUC_THEO_LVCQ($parameters = null) {
        if(!$parameters){
            $parameters = $this->getFilterParameters();
        }
        $pagination = new Pagination(SITE_ROOT . 'dichvucong/tiepnhanonline');
        $parameters_object = Convertor::toArrayObject($parameters);
        $pagination->setCurrentPage($parameters_object->P_PAGE);
        $parameters['P_ITEMS_PER_PAGE'] = $pagination->getItemsPerPage();
        // Nhi issue IGATESUPP-7418
        if($parameters['P_MA_CO_QUAN'] == 'btt_cap_huyen')
        {
            $parameters['P_MA_CAP_THU_TUC'] = 2;
        }
        // sửa theo y/c IGATESUPP-7100
        if(MA_CO_QUAN == null || MA_CO_QUAN == '' || empty(MA_CO_QUAN))
            $sp = new Oracle\StoreProcedure\SELECT_DS_THUTUC_THEO_LVCQ($parameters);
        else
            $sp = new Oracle\StoreProcedure\SELECT_DS_THUTUC_THEO_LVCQ_ALL($parameters);

        $rows = $sp->getDefaultResult();
        if ($rows->count()) {
            $rowcnt = (int) $rows->offsetGet(0)->offsetGet('TOTAL_ROWS');
            $pagination->setTotalItems($rowcnt);
        }
        $pagination->setQueryParams([
            'P_MA_CO_QUAN' => $parameters_object->P_MA_CO_QUAN,
            'P_MA_LINH_VUC' => $parameters_object->P_MA_LINH_VUC,
            'P_MA_MUC_DO' => $parameters_object->P_MA_MUC_DO,
            'P_TEN_THU_TUC' => $parameters_object->P_DIEU_KIEN_MO_RONG,
            'P_MA_CAP_THU_TUC' => $parameters_object->P_MA_CAP_THU_TUC
        ]);
        return $pagination->setData($rows);
    }
     public function getFilterParameters() {
        $P_DIEU_KIEN_MO_RONG = get_request_var('ten-thu-tuc');
        $P_MA_CO_QUAN = get_request_var('ma-co-quan');
        $P_MA_LINH_VUC = get_request_var('ma-linh-vuc');
        $P_MA_MUC_DO = get_request_var('ma-muc-do');
        $P_MA_CAP_THU_TUC = get_request_var('ma-cap-thu-tuc');
        $P_PAGE = get_request_var('page',1);
        if((MA_CO_QUAN == null || $P_MA_CO_QUAN == null) && MA_DON_VI != null)
            $P_MA_CO_QUAN = $this->SELECT_MA_CQ_THEO_MA_DM_DV(MA_DON_VI);
        return array(
            'P_DIEU_KIEN_MO_RONG' => $P_DIEU_KIEN_MO_RONG,
            'P_MA_CO_QUAN' => MA_CO_QUAN ? MA_CO_QUAN : $P_MA_CO_QUAN,
            'P_MA_LINH_VUC' => $P_MA_LINH_VUC,
            'P_MA_MUC_DO' => $P_MA_MUC_DO,
            'P_MA_CAP_THU_TUC' => $P_MA_CAP_THU_TUC,
            'P_PAGE' => $P_PAGE ? $P_PAGE : 1,
            'P_ITEMS_PER_PAGE' => 15
        );
    }
    public function getStt($page,$itemsPerPage){
        return ($page-1)*$itemsPerPage+1;
    }

    public function GET_DS_LINHVUC_COQUAN1($maCQ, $type) {
        $rows = new Oracle\StoreProcedure\GET_DS_LINHVUC_COQUAN(Array('P_MA_CO_QUAN'=>$maCQ,'P_TYPE'=>$type));
        return $rows->getDefaultResult();
    }

    public function updateSoLuongCaThi($soHoSo,$sid) {
        $applier = new Applier($sid);
        $dLCaThi = $applier->sessionGet('DL_CA_THI');
        if ($dLCaThi != null && $dLCaThi != '' && count($dLCaThi) > 0) {
            $dLCaThi['P_SO_HO_SO'] = $soHoSo;
            return (new \Oracle\OracleFunction\UPDATE_DM_CA_THI_SOLUONG_DK($dLCaThi))->getResult();
        }
    }

    public function updateCaThiHanNopLePhi($maHoSo) {
        //Linh them moi - IGATESUPP 7693
        $startTime = date("d-m-Y H:i:s");//khởi tạo
        $h = Model\Entity\System\Parameter::fromId('HAN_NOP_LE_PHI_CA_THI')->getValue();
        //cộng thêm $h giờ
        $dLCaThiHanNopLePhi['P_MA_HO_SO'] = $maHoSo;
        $dLCaThiHanNopLePhi['P_HAN_NOP_LE_PHI'] = date('d-m-Y H:i:s A',strtotime('+'.$h.' hour',strtotime($startTime)));
        (new \Oracle\OracleFunction\UPDATE_CA_THI_HAN_NOP_LP($dLCaThiHanNopLePhi))->getResult();
    }

    public function layDanhMucCoQuanNopHoSo(array $options = []) {
        return new DanhMuc\CoQuan(array_merge($options, [
            'provider' => DanhMuc\CoQuan::DICH_VU_CONG_TRUC_TUYEN
        ]), true, true);
    }

    public function layDanhMucLinhVucNopHoSo(array $options = []) {
        return new DanhMuc\LinhVuc(array_merge($options, [
            'provider' => DanhMuc\LinhVuc::DICH_VU_CONG_TRUC_TUYEN
        ]), true, true);
    }

    //IGATESUPP-8128 Nhi 14/3/2019 xử lý thêm select thêm các dữ liệu của trường dữ liệu, của form thông tin ng nộp động
    public function layDanhSachDuLieuTDL($sid, $eform_id, $maTDL, $maHS)
    {
        return (new Oracle\Package\BCA_DICH_VU_CONG())->SELECT_GIA_TRI_TDL_N([
            'P_MA_TRUONG_DU_LIEU' => $maTDL,
            'P_SID' => $sid,
            'P_EFORM_ID' => $eform_id,
            'P_MA_HO_SO' => $maHS
        ]);
    }

    public function layDanhMucCapThuTuc(array $options = []) {
        return new DanhMuc\CapThuTuc(array_merge($options, []));
    }

    public function getHoSoVilisFromSession($sid){
        $applier = new Applier($sid);
        return $applier->sessionGet(Applier::HS_VILIS);
    }

    public function getGTVilisFromSession($sid){
        $applier = new Applier($sid);
        $giayToDinhKemVilis = $applier->sessionGet(Applier::GT_VILIS);
        return $giayToDinhKemVilis['luuIgate'];
    }

    public function getDSGiayToVilis($maHoSoVilis){
        if(!empty($maHoSoVilis)){
            return null;
        }
        return (new \Oracle\StoreProcedure\VILIS_SELECT_GIAY_TO([
            'P_MA_HO_SO_VILIS' => $maHoSoVilis
        ]))->getDefaultResult();
    }



    public function SELECT_TAI_KHOAN_NGAN_HANG ($P_MA_CONG_DAN) {
        return ( new \Oracle\StoreProcedure\SELECT_TAI_KHOAN_NGAN_HANG(['P_MA_CONG_DAN' => $P_MA_CONG_DAN]))->getDefaultResult(0);
    }

    public function sign ($str_xml) {
        $dsig = new \Model\VietinBank\XmlDigitalSignature();

        $dsig
            ->setCryptoAlgorithm(\Model\VietinBank\XmlDigitalSignature::RSA_ALGORITHM)
            ->setDigestMethod(\Model\VietinBank\XmlDigitalSignature::DIGEST_SHA1);

        $VIETINBANK_PATH_PRIVATE_KEY = Model\Entity\System\Parameter::fromId('VIETINBANK_PATH_PRIVATE_KEY')->getValue();
        $VIETINBANK_PASSWORD_PRIVATE_KEY = Model\Entity\System\Parameter::fromId('VIETINBANK_PASSWORD_PRIVATE_KEY')->getValue();

        if ($VIETINBANK_PATH_PRIVATE_KEY != '' && !empty($VIETINBANK_PATH_PRIVATE_KEY) && $VIETINBANK_PASSWORD_PRIVATE_KEY != '' && !empty($VIETINBANK_PASSWORD_PRIVATE_KEY)) {

            // load the private and public keys
            try
            {
                $dsig->loadPrivateKeyP12($VIETINBANK_PATH_PRIVATE_KEY, $VIETINBANK_PASSWORD_PRIVATE_KEY);
                $dsig->createKeyInfo();
            }
            catch (\UnexpectedValueException $e)
            {
                print_r($e);
                exit(1);
            }

            $messageDoc = new \DOMDocument();
            $messageDoc->loadXML($str_xml);

            $node = $messageDoc->getElementsByTagName('MSG_ID')->item(0);

            try
            {
                $dsig->setMessage($messageDoc);
                $dsig->addObject(null, null, false);
                $sig = $dsig->sign();
                $check = $dsig->verify();
            }
            catch (\UnexpectedValueException $e)
            {
                print_r($e);
                exit(1);
            }

            $xmlMessageDoc = $dsig->getMessageDocument();

            if ($sig == true and $check == true) {
                return ['status' => 'success', 'xml' => $xmlMessageDoc, 'message' => 'OK'];
            } else {
                return ['status' => 'error', 'xml' => $xmlMessageDoc, 'message' => 'Ký số hoặc xác nhận ký số thất bại!'];
            }

        } else {
            return ['status' => 'error', 'message' => 'Chưa cấu hình file private key hoặc pass mở file!'];
        }
    }

    public function checkVerify ($input) {
        $dsig = new \Model\VietinBank\XmlDigitalSignature();

        $dsig
            ->setCryptoAlgorithm(\Model\VietinBank\XmlDigitalSignature::RSA_ALGORITHM)
            ->setDigestMethod(\Model\VietinBank\XmlDigitalSignature::DIGEST_SHA1);

        $VIETINBANK_PATH_PUBLIC_KEY = Model\Entity\System\Parameter::fromId('VIETINBANK_PATH_PUBLIC_KEY')->getValue();

        if ($VIETINBANK_PATH_PUBLIC_KEY != '' && !empty($VIETINBANK_PATH_PUBLIC_KEY)) {

            // load the private and public keys
            try
            {
                $dsig->loadPublicKeyCer($VIETINBANK_PATH_PUBLIC_KEY);
            }
            catch (\UnexpectedValueException $e)
            {
                print_r($e);
                exit(1);
            }

            $fakeXml = new \DOMDocument('1.0', 'UTF-8');
            $fakeXml->loadXML($input);

            $messageDom = new \DOMDocument('1.0', 'UTF-8');
            $root = $messageDom->createElement('Message');

            $root->appendChild($messageDom->importNode($fakeXml->getElementsByTagName('Header')->item(0), true));
            $root->appendChild($messageDom->importNode($fakeXml->getElementsByTagName('Data')->item(0), true));

            $messageDom->appendChild($root);

            $signDom = new \DOMDocument('1.0', 'UTF-8');
            $signDom->appendChild($signDom->importNode($fakeXml->getElementsByTagName('Signature')->item(0), true));

            $signatureValue = $fakeXml->getElementsByTagName('SignatureValue')->item(0);

            try
            {
                $dsig->setMessage($messageDom);
                $dsig->setDoc($signDom);
                $checkVerify = $dsig->checkVerify($signatureValue->nodeValue);
                return ['status' => $checkVerify, 'message' => 'OK'];
            }
            catch (\UnexpectedValueException $e)
            {
                print_r($e);
                exit(1);
            }
        } else {
            return ['status' => 'error', 'message' => 'Chưa cấu hình file public key!'];
        }
    }

    public function SaveGiayToBoSung(){
        $data = $this->getPostData();
        $hoSo = Entity\HoSo::fromMaHoSoOnline((int)$data['sid']);
        $giayTo = $this->makeDSGiayToBoSung();
        $arrMaGiayToOld = $this->makeDSGiayToBoSungOld();
        $arrMaGiayTo = array();
        foreach ($giayTo as $key => $value) {
            $maHS = (new \Oracle\StoreProcedure\SELECT_HO_SO_BY_SHS_ONL(Array('P_MA_HO_SO_ONLINE' => $data['sid'])))->getDefaultResult();
            $arr = Array(
              'P_MA_HO_SO' => $maHS['P_MA_HO_SO'],
              'P_MA_HO_SO_ONLINE' => $data['sid'],
              'P_TEN_GIAY_TO' => $value['filename'],
              'P_FILE_GIAY_TO' => $value['filepath'],
              'P_SO_BAN' => 1,
              'P_NGAY_TAO' => date("d/m/Y H:i:s"),
              'P_MA_LOAI_GIAY_TO_KHAC' => $value['type'],
              'P_LE_PHI' => '',
              'P_HO_TEN_NGUOI_KI' => '',
              'P_MA_CO_GIAY_TO_KHAC' => $value['id']);
            $ketQua = (new \Oracle\OracleFunction\UPDATE_HS_CO_GIAY_TO_KHAC($arr))->getResult();
            array_push($arrMaGiayTo, $value['id']);
        }
        if($arrMaGiayToOld){
            foreach ($arrMaGiayToOld as $maGiayToOld) {
                if(!in_array($maGiayToOld, $arrMaGiayTo)){
                    $delteGiayTo = (new \Oracle\OracleFunction\DELETE_HS_CO_GIAY_TO_KHAC(Array('P_MA_CO_GIAY_TO_KHAC' => $maGiayToOld)))->getResult();
                }

            }
        }
        if($ketQua > 0)
        {
            $cauHinhSMS = (new \Oracle\StoreProcedure\SELECT_CONTENT_SMS_QTTT([
                'P_MA_QTTT' => $hoSo->getMaQTTT(),
                'P_TYPE' => 10 // Cập nhật thành công gửi thông báo cho cán bộ đang xử lý.
            ]))->getDefaultResult(0);
            $canBo = Entity\CanBo::fromMaCanBo((int)$hoSo->getMaCanBoDangThucHien());
            if(!empty($canBo->getDiDong()) && !empty($cauHinhSMS['CONTENT'])){
                if($cauHinhSMS['ID'] != 0){
                    $noiDungSMS = $hoSo->replaceSMS($cauHinhSMS['CONTENT']);
                    $sms = new SMS\SMS();
                    $sms->setMobile($canBo->getDiDong());
                    $sms->setContent($noiDungSMS);
                    $brandName = new SMS\Brandname($sms);
                    $rs = $brandName->send();
                }
            }
            $cauHinhEmail = (new \Oracle\StoreProcedure\SELECT_CONTENT_MAIL_QTTT([
                'P_MA_QTTT' => $hoSo->getMaQTTT(),
                'P_TYPE' => 10 // Cập nhật thành công gửi thông báo cho cán bộ đang xử lý.
            ]))->getDefaultResult(0);
            if(!empty($cauHinhEmail['CONTENT']) &&  !empty($canBo->getEmail()) ){
                $noiDungMAIL = htmlspecialchars_decode($hoSo->replaceVariableHS($cauHinhEmail['CONTENT']));
                $this->GUI_EMAIL_QUY_TRINH($canBo->getEmail(), $cauHinhEmail['TITLE'], $noiDungMAIL, '', true);
            }
            echo "<script type='text/javascript'>alert('Cập nhật thông tin thành công');</script>";
            header(sprintf('Location: %sbo-cong-an/tiep-nhan-online/nhap-thong-tin-ho-so-online?sid=%s&ms=1', SITE_ROOT, $data['sid']));
        }
        else
        {
            echo "<script type='text/javascript'>alert('Cập nhật thông tin thất bại');</script>";
            header(sprintf('Location: %sbo-cong-an/tiep-nhan-online/nhap-thong-tin-ho-so-online?sid=%s&ms=0', SITE_ROOT, $data['sid']));
        }
    }

    public function makeDSGiayToBoSung(){
        $dsGiayToKhacPost = $this->getPostData()['dsGiayToBoSung'];
        $giayTo = Array();
        foreach($dsGiayToKhacPost as $giayToPost){
            $arr = Array();
            if($giayToPost['filename']){
                $newfilepath = System::getDefaultUploader()->setPostname($giayToPost['postname'])->upload();
                $arr['filepath'] = $newfilepath;
                $arr['filetitle'] = $giayToPost['filetitle'];
                $arr['id'] = $giayToPost['id'];
                $arr['postname'] = $giayToPost['postname'];
                $arr['type'] = $giayToPost['type'];
                $arr['filename'] = $giayToPost['filename'];
            }
            array_push($giayTo, $arr);
        }
        return $giayTo;
    }
    public function makeDSGiayToBoSungOld(){
        $dsGiayToKhacPost = $this->getPostData()['dsGiayToBoSungOld'];
        $maGiayToOld = Array();
        foreach($dsGiayToKhacPost as $giayToPost){
                $id = $giayToPost['id'];
            array_push($maGiayToOld, $id);
        }
        return $maGiayToOld;
    }
    public function getSoBienNhan($soHoSo){
        if($soHoSo){
            $sp = new \Oracle\StoreProcedure\LAN_SELECT_GIAY_BIEN_NHAN([
               'P_SO_HO_SO' =>  $soHoSo
            ]);
            return $sp->getDefaultResult(0);
        }
        return [];
    }

    /**
     * Insert HS Vilis và giấy tờ vào iGate (trường hợp cộng dân nộp hs online)
     * @param $applier
     * @param $hoSoOnline
     * @param $hoSoVilis
     * @return ResultInfo
     */
    public function insertVilis($applier,$hoSoOnline,$hoSoVilis) {
        $soBienNhanVilis = '';
        $vilisLuuGiayToDinhKem = Entity\System\Parameter::fromId('vilis_LuuGiayToDinhKem')->getValue();
        $giayToDinhKemVilis = $applier->sessionGet(Applier::GT_VILIS);
        $soLuongGiayTo = count($giayToDinhKemVilis['luuIgate']);
        $arrInsertHoSoVilis = [
            'P_MA_HO_SO_MOT_CUA' => null,
            'P_MA_QT_VILIS' => $hoSoVilis->getMaVilis(),
            'P_SO_HO_SO_VILIS' => $soBienNhanVilis,
            'P_SO_THU_TU_THUA' => $hoSoVilis->getSoThuTuThua(),
            'P_SO_HIEU_TO_BAN_DO' => $hoSoVilis->getSoHieuToBanDo(),
            'P_MA_LOAI_GIAO_DICH' => $hoSoVilis->getMaLoaiGiaoDich(),
            'P_MA_LOAI_HO_SO_GIAO_DICH' => $hoSoVilis->getMaLoaiHoSoGiaoDich(),
            'P_MA_PHUONG_XA_VILIS' => $hoSoVilis->getMaPhuongXaVilis(),
            'P_MA_CAN_BO_VILIS' => null,
            'P_TU_CACH' => $hoSoVilis->getTuCach(),
            'P_LA_TO_CHUC' => $hoSoVilis->getLaToChuc(),
            'P_GHI_CHU_TU_CACH' => $hoSoVilis->getGhiChuTuCach(),
            'P_TEN_LOAI_GIAO_DICH' => $hoSoVilis->getTenLoaiGiaoDich(),
            'P_TEN_LOAI_HO_SO_GIAO_DICH' => $hoSoVilis->getTenLoaiHoSoGiaoDich(),
            'P_MA_HO_SO_ONLINE' => $hoSoOnline->getMaHoSo()
        ];
        $result = (new Package\MC_VILIS())->INSERT_HO_SO_VILIS2($arrInsertHoSoVilis);
        if ($result->getString('P_VAL') === 'THANH_CONG') {
            $rsLuuGiayToDinhKemVaoIgateResult = true;
            if ($vilisLuuGiayToDinhKem && $soLuongGiayTo > 0) {
                foreach ($giayToDinhKemVilis['luuIgate'] as $item) {
                    $arrInsertGiayToVilis = [
                        'P_MA_HO_SO_VILIS' => $result->getInt('P_MA_HO_SO_VILIS'),
                        'P_MA_GIAY_TO_VILIS' => $item['giayToMauViLISId'],
                        'P_TEN_VAN_BAN' => $item['tenVanBan'],
                        'P_SO_BAN_CHINH' => $item['soBanChinh'],
                        'P_SO_BAN_SAO' => $item['soBanSao'],
                        'P_TEN_FILE' => $item['tenFile'],
                        'P_FILE_PATH' => $item['igatepath'],
                    ];
                    $rsLuuGiayToDinhKemVaoIgate = (new Package\MC_VILIS())->INS_GIAY_TO_VILIS($arrInsertGiayToVilis);
                    if ($rsLuuGiayToDinhKemVaoIgate->P_VAL !== 'THANH_CONG') {
                        $rsLuuGiayToDinhKemVaoIgateResult = false;
                        break;
                    }
                }
            }
            if ($rsLuuGiayToDinhKemVaoIgateResult) {
                // insert hs vilis và giấy tờ thành công
                return new ResultInfo(1,'Insert hồ sơ Vilis và giấy tờ thành công.');
            }
            else {
                // insert giấy tờ vilis lỗi
                $message = 'Lưu giấy tờ đính kèm Vilis vào iGate thất bại (công dân nộp hồ sơ).';
                $jsonParams = json_encode($arrInsertGiayToVilis, JSON_UNESCAPED_UNICODE);
                System\Logger::add(substr(sprintf('[TNHSVL-CDN-FILE-INSERT-MC][FAILED][%s] %s', $hoSoOnline->getMaQttt(), $message.$rsLuuGiayToDinhKemVaoIgate->P_VAL),0,2000),['string' => $jsonParams]);
                return new ResultInfo(0,'Lưu giấy tờ đính kèm Vilis vào iGate thất bại (công dân nộp hồ sơ).');
            }
        }
        else {
            // insert hs vilis vào iGate lỗi
            $message = 'Lưu hồ sơ Vilis vào iGate không thành công (công dân nộp hồ sơ). ';
            $jsonParams = json_encode($arrInsertHoSoVilis, JSON_UNESCAPED_UNICODE);
            System\Logger::add(substr(sprintf('[TNHSVL-CDN-INSERT-MC][FAILED][%s] %s', $hoSoOnline->getMaQttt(), $message.$result->getString('P_VAL')),0,2000),['string' => $jsonParams]);
            return new ResultInfo(0,'Lưu hồ sơ Vilis vào iGate không thành công (công dân nộp hồ sơ).');
        }
    }

    /**
     * Update HS Vilis và giấy tờ vào iGate (trường hợp cộng dân nộp hs online)
     * @param $applier
     * @param $hoSoOnline
     * @param $hoSoVilis
     * @return ResultInfo
     */
    public function updateVilis($applier,$hoSoOnline,$hoSoVilis) {
        $soBienNhanVilis = '';
        $vilisLuuGiayToDinhKem = Entity\System\Parameter::fromId('vilis_LuuGiayToDinhKem')->getValue();
        $giayToDinhKemVilis = $applier->sessionGet(Applier::GT_VILIS);
        $soLuongGiayTo = count($giayToDinhKemVilis['luuIgate']);
        // cập nhật thông tin hs Vilis
        $arrUpdateHoSoVilis = [
            'P_MA_HO_SO_ONLINE' => $hoSoOnline->getMaHoSo(),
            'P_MA_VILIS' => $hoSoVilis->getMaVilis(),  // mã quy trình Vilis
            'P_SO_THU_TU_THUA' => $hoSoVilis->getSoThuTuThua(),
            'P_SO_HIEU_TO_BAN_DO' => $hoSoVilis->getSoHieuToBanDo(),
            'P_MA_LOAI_GIAO_DICH' => $hoSoVilis->getMaLoaiGiaoDich(),
            'P_MA_LOAI_HO_SO_GIAO_DICH' => $hoSoVilis->getMaLoaiHoSoGiaoDich(),
            'P_MA_PHUONG_XA_VILIS' => $hoSoVilis->getMaPhuongXaVilis(),
            'P_MA_CAN_BO_VILIS' => null,
            'P_TRANG_THAI_HO_SO_VILIS' => null,
            'P_MA_CAN_BO_CHUYEN' => null,
            'P_MA_TRANG_THAI_VILIS' => null,
            'P_TEN_TRANG_THAI_VILIS' => null,
            'P_TU_CACH' => $hoSoVilis->getTuCach(),
            'P_LA_TO_CHUC' => $hoSoVilis->getLaToChuc(),
            'P_GHI_CHU_TU_CACH' => $hoSoVilis->getGhiChuTuCach(),
            'P_TEN_LOAI_GIAO_DICH' => $hoSoVilis->getTenLoaiGiaoDich(),
            'P_TEN_LOAI_HO_SO_GIAO_DICH' => $hoSoVilis->getTenLoaiHoSoGiaoDich(),
        ];
        $result = (new Package\MC_VILIS())->UPD_HSVILIS_BY_MA_HS_ONLINE($arrUpdateHoSoVilis);
        if ($result->P_VAL !== 'THANH_CONG') {
            $message = 'Cập nhật hồ sơ Vilis vào iGate không thành công (công dân nộp hồ sơ).';
            $jsonParams = json_encode($arrUpdateHoSoVilis, JSON_UNESCAPED_UNICODE);
            System\Logger::add(substr(sprintf('[TNHSVL-CDN-UPDATE-MC][FAILED][%s] %s', $hoSoOnline->getMaQttt(), $message.$result->P_VAL),0,2000),['string' => $jsonParams]);
            return new ResultInfo(0,'Cập nhật hồ sơ Vilis vào iGate không thành công (công dân nộp hồ sơ).');
        }
        // xóa giấy tờ cũ
        $rsXoaGiayTo = (new Package\MC_VILIS())->DEL_GIAYTO_BY_MA_HS_ONLINE([
            'P_MA_HO_SO_ONLINE' => $hoSoOnline->getMaHoSo()
        ]);
        if ($rsXoaGiayTo !== 'THANH_CONG') {
            $message = 'Xóa giấy tờ cũ không thành công (công dân cập nhật hồ sơ). ';
            System\Logger::add(substr(sprintf('[TNHSVL-CDN-DELETE-GT-MC][FAILED][%s] %s', $hoSoOnline->getMaHoSo(), $message.$rsXoaGiayTo),0,2000));
            return new ResultInfo(0,'Xóa giấy tờ cũ không thành công (công dân cập nhật hồ sơ). ');
        }
        echo 'aaaaaaaaa';
        // lưu lại giấy tờ mới
        $rsLuuGiayToDinhKemVaoIgateResult = true;
        if ($vilisLuuGiayToDinhKem && $soLuongGiayTo > 0) {
            foreach ($giayToDinhKemVilis['luuIgate'] as $item) {
                $arrInsertGiayToVilis = [
                    'P_MA_HO_SO_VILIS' => $result->P_MA_HO_SO_VILIS,
                    'P_MA_GIAY_TO_VILIS' => $item['giayToMauViLISId'],
                    'P_TEN_VAN_BAN' => $item['tenVanBan'],
                    'P_SO_BAN_CHINH' => $item['soBanChinh'],
                    'P_SO_BAN_SAO' => $item['soBanSao'],
                    'P_TEN_FILE' => $item['tenFile'],
                    'P_FILE_PATH' => $item['igatepath'],
                ];
                $rsLuuGiayToDinhKemVaoIgate = (new Package\MC_VILIS())->INS_GIAY_TO_VILIS($arrInsertGiayToVilis);
                if ($rsLuuGiayToDinhKemVaoIgate->P_VAL !== 'THANH_CONG') {
                    $rsLuuGiayToDinhKemVaoIgateResult = false;
                    break;
                }
            }
        }
        if (!$rsLuuGiayToDinhKemVaoIgateResult) {
            // insrt giấy tờ vilis lỗi
            $message = 'Lưu giấy tờ đính kèm Vilis vào iGate thất bại (công dân nộp hồ sơ).';
            $jsonParams = json_encode($arrInsertGiayToVilis, JSON_UNESCAPED_UNICODE);
            System\Logger::add(substr(sprintf('[TNHSVL-CDN-FILE-INSERT-MC][FAILED][%s] %s', $hoSoOnline->getMaQttt(), $message.$rsLuuGiayToDinhKemVaoIgate->P_VAL),0,2000),['string' => $jsonParams]);
            return new ResultInfo(0,'Lưu giấy tờ đính kèm Vilis vào iGate thất bại (công dân nộp hồ sơ).');
        }

        return new ResultInfo(1,'Insert hồ sơ Vilis và giấy tờ thành công.');
    }
    public function SELECT_CAP_THU_TUC () {
        $parameters = Array();
        $parameters['FUNCTION_NAME'] = 'SELECT_CAP_THU_TUC';
        $cacheKey = $this->createCacheName($parameters);
        $cache = $this->createCache('cache/frontend/dichvucong/tiepnhanonline', 8);
        if ($cache->hasItem($cacheKey)) {
            $rows = unserialize($cache->getItem($cacheKey));
        } else {
            $package = new Package\MC_TT();
            $rows = $package->SELECT_CAP_THU_TUC();
            $cache->setItem($cacheKey, serialize($rows));
        }
        return $rows;
    }

    public function DG_SELECT_BUOC_DANH_GIA ($P_SO_HO_SO) {
        return ( new \Oracle\StoreProcedure\DG_SELECT_BUOC_DANH_GIA(['P_SO_HO_SO' => $P_SO_HO_SO]))->getDefaultResult();
    }
    public function GET_TH_MUC_THU_THU_TUC_TVH($ma_thu_tuc) {
        $rows = new Oracle\StoreProcedure\GET_TH_MUC_THU_THU_TUC_TVH(array(
            'P_MA_THU_TUC' => $ma_thu_tuc
        ));
        return $rows->getDefaultResult();
    }
}
