<?php

namespace Model\Entity;

use DateTime;
use Oracle\Package;
use Oracle\Connection;
use Oracle\OracleFunction;
use Oracle\StoreProcedure;
use Model\Entity;
use Model\DanhMuc;
use Model\VNPost;
// use Model\HoSo\BieuMau;
use Model\VNPT\SMS;
use Model\System;
use Zend\Mail;
use Zend\Mime;
use Zend\Stdlib\ArrayObject;
use Nth\ResultInfo;
use Nth\Helper\Characters;
use Model\Entity\System as Sys;
use Model;

use Zend\Http\Request;
use Zend\Http\Client;
use Zend\Stdlib\Parameters;
use Nth\File\Folder;
use Zend\Cache\StorageFactory;
use Zend\Debug\Debug;
use Model\DichVuCong\Applier;

class HoSoOnline extends Entity\HoSo {

    const LuuNhungKhongNop = 'LUU_NHUNG_KHONG_NOP';
    const DaNop = 'DA_NOP';
    const DangCapNhat = 'DANG_CAP_NHAT';
    const DangTiepNhan = 'DANG_TIEP_NHAN';
    const DaTiepNhan = 'DA_TIEP_NHAN';
    const DaHuy = 'DA_HUY';
    const ChoBoSung = 'CHO_BO_SUNG';
    const DaBoSungVaNopLai = 'DA_BO_SUNG_VA_NOP_LAI';
    const CongDanXoa = 'CONG_DAN_XOA';
    const DangTamDung = 'DANG_TAM_DUNG';

    const STATUS_NEW = 0;
    const STATUS_PICKED_UP = 1;
    const STATUS_PICK_UP_CANCELED = 3;
    const STATUS_PICK_UP_FAILED = 4;

    /**
     * Properties
     */
    private $maHoSoTiepNhan;
    private $ngayHuy;
    private $fileHuy;
    private $maCanBoTxhsOnline;
    private $fileYCBS;
    private $dmGiayToKhac;
    private $hsNopTuCDVCQG;
    private $maHoSoCDVCQG;
    private $ngayYCBS;
    /**
     * Properties of relative entities
     */

    private $canBoTxhsOnline;
    private $chuHoSo;

    const DANH_SACH_HO_SO = 'https://api.laocai.gov.vn/NGSP-CSDLDKDN/1.0/danhSachHoSo';
    const CHI_TIET_DOANH_NGHIEP = 'https://api.laocai.gov.vn/NGSP-CSDLDKDN/1.0/chiTietDoanhNghiep';
    const TOKEN_ACCESS = 'https://api.laocai.gov.vn/token';
    const CONTENT_GET_TOKEN = "grant_type=client_credentials&client_secret=TO3bVokrhZWPsN9yGdn_VjlyLDsa&client_id=v3bde7ExwFXmj0f73pl4n5eJNDQa";
    const CONTENT_HEADER_TOKEN = 'application/x-www-form-urlencoded';
    const SITE_ID = 119; // LÀO CAI
    const CACHE_KEY = 'trangchu_tracuu_doanhnghiep_storeCache';
    const FOLDER_SAVE_CACHE = 'cache/frontend/dichvucong/home';
    const  CACH_TIME = 24;

    public function getMaHoSoTiepNhan() {
        return $this->maHoSoTiepNhan;
    }

    public function getNgayHuy() {
        return $this->ngayHuy;
    }

    public function getNgayYCBS() {
        return $this->ngayYCBS;
    }

    public function getFileHuy() {
        return $this->fileHuy;
    }


    public function getMaCanBoTxhsOnline() {
        return $this->maCanBoTxhsOnline;
    }

    public function getCanBoTxhsOnline() {
        return $this->canBoTxhsOnline;
    }

    public function getFileYCBS(){
        return $this->fileYCBS;
    }

    public function setMaHoSoTiepNhan($maHoSoTiepNhan) {
        $this->maHoSoTiepNhan = $maHoSoTiepNhan;
    }

    public function setNgayHuy(DateTime $ngayHuy = null) {
        $this->ngayHuy = $ngayHuy;
    }

    public function setNgayYCBS($ngayYCBS) {
        $this->ngayYCBS = $ngayYCBS;
    }

    public function setFileHuy($fileHuy) {
        $this->fileHuy = $fileHuy;
    }

    public function sethsNopTuCDVCQG($hsNopTuCDVCQG) {
        $this->hsNopTuCDVCQG = $hsNopTuCDVCQG;
    }
    public function gethsNopTuCDVCQG() {
        return $this->hsNopTuCDVCQG;
    }
    public function setmaHoSoCDVCQG($maHoSoCDVCQG) {
        $this->maHoSoCDVCQG = $maHoSoCDVCQG;
    }
    public function getmaHoSoCDVCQG() {
        return $this->maHoSoCDVCQG;
    }

    public function setMaCanBoTxhsOnline($maCanBoTxhsOnline) {
        $this->maCanBoTxhsOnline = $maCanBoTxhsOnline;
    }

    public function setCanBoTxhsOnline(Entity\CanBo $canBo = null) {
        $this->canBoTxhsOnline = $canBo;
    }

    public function setFileYCBS($fileYCBS){
        $this->fileYCBS = $fileYCBS;
    }

    public function getChuHoSo() {
        return $this->chuHoSo;
    }

    public function setChuHoSo(Entity\ChuHoSo $chuHoSo = null) {
        $this->chuHoSo = $chuHoSo;
    }

    public function getDmGiayToKhac($isApplyOnline = true) {
        if ($this->dmGiayToKhac) {
            return $this->dmGiayToKhac;
        }
        if ($isApplyOnline) {
            return new DanhMuc\GiayToKhacCuaHoSo([
                'maHoSoOnline' => $this->getMaHoSo()
            ]);
        }
        return new DanhMuc\GiayToKhacCuaHoSo([
            'maHoSo' => $this->getMaHoSo()
        ]);
    }

    public function setDmGiayToKhac(DanhMuc\GiayToKhacCuaHoSo $dmGiayToKhac) {
        $this->dmGiayToKhac = $dmGiayToKhac;
    }

    public function updateDmGiayToKhac($isApplyOnline = true) {
        if ($maHoSo = $this->getMaHoSo()) {
            foreach ($this->dmGiayToKhac->getItems() as $item) {
                if ($isApplyOnline) {
                    $item->setMaHoSoOnline($maHoSo);
                }
                else {
                    $item->setMaHoSo($maHoSo);
                }
                $item->update();
            }
        }
        return true;
    }

    /**
     * Kiem tra ho so co yeu cau thu gom khong
     * Cac dieu kien kiem tra:
     * - Ho so phai o trang thai DA_NOP hoac DA_BO_SUNG_VA_NOP_LAI
     * - Le phi ho so lon hon 0
     * - Ho so co dang ky thu gom
     * - Ho so co dang ky dich vu thu ho le phi
     *
     * @return boolean
     */
    public function coYeuCauThuGom () {
        return (int) $this->getMaHinhThucNop() === 1 && in_array($this->getTrangThaiHoSo(), ['DA_NOP', 'DA_BO_SUNG_VA_NOP_LAI']);
    }

    /**
     * Kiem tra ho so da co yeu cau thu gom chua
     * Bao gom co lien thong hoac khong lien thong buu dien
     *
     * @return boolean
     */
    public function daYeuCauThuGom () {
        return (int) $this->getMaHangGuiThuGom() > 0;
    }

    /**
     * Gui yeu cau thu gom ho so
     * Cap nhat du lieu thu gom vao he thong
     * Neu co lien thong buu dien goi webservice gui yeu cau qua buu dien
     * Neu yeu cau da gui qua buu dien thuc hien huy yeu cau va gui lai
     *
     * @return boolean
     */
    public function guiYeuCauThuGom () {
        if ($this->daYeuCauThuGom() && !$this->huyYeuCauThuGom()) {
            return false;
        }
        $maHoSo = $this->getMaHoSo();
        $package = new Package\VNPOST();
        $data = ['P_MA_HO_SO_ONLINE' => $maHoSo];
        $rs = $package->YEU_CAU_THU_GOM($data);
        if ($rs === 1) {
            $maHangGuiThuGom = $data['P_MA_HANG_GUI'];
            $this->setMaHangGuiThuGom($maHangGuiThuGom);
            $user = VNPost\User::fromMaHoSoOnline($maHoSo);
            $vnpostYeuCau = Entity\VNPost\YeuCau::fromMaHangGui($maHangGuiThuGom);
            if (Entity\CoQuan::fromMaHoSoOnline($maHoSo)->coLienThongBuuDien()) {
                return $vnpostYeuCau->guiSangBuuDien($user);
            }
            $vnpostYeuCau->updateTrangThaiTo(0);
            return true;
        }
        return false;
    }

    /**
     * Huy yeu cau thu gom ho so
     * Kiem tra neu da gui yeu cau sang buu dien thi huy yeu cau phia buu dien
     * Neu thanh cong se huy yeu cau trong he thong
     *
     * @return boolean
     */
    public function huyYeuCauThuGom() {
        $maHoSo = $this->getMaHoSo();
        $user = VNPost\User::fromMaHoSoOnline($maHoSo);
        $vnpostYeuCau = Entity\VNPost\YeuCau::fromMaHangGui($this->getMaHangGuiThuGom());
        if (!$vnpostYeuCau->exists()) {
            $this->INSERT_LOG('[VNPOST][HO_SO_ONLINE][' . $this->getSoHoSo() . ']DON HANG KHONG TON TAI, KHONG THE HUY YEU CAU THU GOM', 0);
            return false;
        }

        //Chia làm 3 trường hợp
        $LGSP_VNPOST_ADAPTER_URL_API = Model\Entity\System\Parameter::fromId('LGSP_VNPOST_ADAPTER_URL_API')->getValue();
        $LGSP_NGSP_API_CONNECT_VNPOST = Model\Entity\System\Parameter::fromId('LGSP_NGSP_API_CONNECT_VNPOST')->getValueArray();
        $LGSP_VNPOST_GETPRICE_USER = Model\Entity\System\Parameter::fromId('LGSP_VNPOST_GETPRICE_USER')->getValue();

        // VNPOST LCI - Lấy mã CustomerCode theo mã CRM do LCI quy định
        $package2 = new Package\VNPOST();
        $LGSP_VNPOST_LCI_CONNECT = Model\Entity\System\Parameter::fromId('LGSP_VNPOST_LCI_CONNECT_CRM')->getValue();
        if($LGSP_VNPOST_LCI_CONNECT == 1) {
            $LGSP_VNPOST_CRM = $package2->SELECT_MA_CRM_THEO_MA_CQ(['P_MA_CO_QUAN' => $this->getMaCoQuan()]);
            if($LGSP_VNPOST_CRM) {
                $LGSP_VNPOST_GETPRICE_USER = $LGSP_VNPOST_CRM;
            }
        }

        if (!empty($LGSP_NGSP_API_CONNECT_VNPOST['API_CANCEL_ORDER'])) {
            $USE_BEARER_TOKEN_LGSP_SAVIS = Model\Entity\System\Parameter::fromId('USE_BEARER_TOKEN_LGSP_SAVIS')->getValue();
            if (!empty($USE_BEARER_TOKEN_LGSP_SAVIS)) {
                    $token_access = $USE_BEARER_TOKEN_LGSP_SAVIS;
            }else{
                    $datat = $this->callAPIGetKeyTokenBTE();
                    $token_access = $datat['access_token'];
            }
            $body_content2 = array(
                'CustomerCode' => $LGSP_VNPOST_GETPRICE_USER,
                'OrderNumber'  => $this->getMaHangGuiThuGom()
            );
            $adapter2 = new Client\Adapter\Curl();
            $adapter2->setOptions(array(
                'curloptions' => array(
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false
                )
            ));
            $client2 = new Client();
            $client2->setAdapter($adapter2);
            $client2->setOptions(array(
                'maxredirects' => 0,
                'timeout' => 30
            ));
            $client2->setMethod('GET');
            $client2->setHeaders(array(
                'Authorization' => 'Bearer ' . $token_access,
                'Content-Type' => 'application/json'
            ));

            $LGSP_NGSP_API_CONNECT_VNPOST['API_CANCEL_ORDER'] = $LGSP_NGSP_API_CONNECT_VNPOST['API_CANCEL_ORDER'] . '?CustomerCode=' . $LGSP_VNPOST_GETPRICE_USER . '&OrderNumber=' . $this->getMaHangGuiThuGom();
            $client2->setUri($LGSP_NGSP_API_CONNECT_VNPOST['API_CANCEL_ORDER']);
            $response2 = $client2->send();
            if ($response2->isSuccess()) {
                $data2 = json_decode($response2->getContent(), true);
                if ($data2['Status'] == '110') {

                    $this->INSERT_LOG('[VNPOST][HO_SO_ONLINE][' . $this->getSoHoSo() . ']XOA YEU CAU DON HANG HS VNPOST NGSP THANH CONG!'
                                . 'Thong tin API : ' . json_encode($body_content2)
                                . ', Ket qua tra ve : Status=>' . $data2["Status"]
                                . ' va Message=>' . $data2["Message"], 0);
                } else {
                    $this->INSERT_LOG('[VNPOST][HO_SO_ONLINE][' . $this->getSoHoSo() . ']XOA YEU CAU DON HANG HS VNPOST NGSP THAT BAI!'
                                . 'Thong tin API : ' . json_encode($body_content2)
                                . ', Ket qua tra ve : Status=>' . $data2["Status"]
                                . ' va Message=>' . $data2["Message"], 0);
                    return false;

                }
            } else {
                $this->INSERT_LOG('[VNPOST][HO_SO_ONLINE][' . $this->getSoHoSo() . ']XOA YEU CAU DON HANG HS VNPOST NGSP THAT BAI!'
                            . 'Thong tin API : ' . json_encode($body_content2)
                            . ', Ket qua tra ve : ' . json_encode($response2->getContent(), true), 0);
                return false;
            }

        } elseif (!empty($LGSP_VNPOST_ADAPTER_URL_API)) {//VNPOST LCI

            $LGSP_VNPOST_VTU_CONNECT = Model\Entity\System\Parameter::fromId('LGSP_VNPOST_VTU_CONNECT')->getValue();

            $datat = $this->callAPIGetKeyToken();
            $token_access = $datat['access_token'];

            //API huy ho so
            if ($LGSP_VNPOST_VTU_CONNECT == '1') {//VNPOST VTU
                $body_content2 = array(
                    'customerCode' => $LGSP_VNPOST_GETPRICE_USER,
                    'orderNumber' => $this->getMaHangGuiThuGom()
                );
            }else{// VNPOST LCI
                $body_content2 = array(
                    'maKhachHang' => $LGSP_VNPOST_GETPRICE_USER,
                    'soDonHang' => $this->getMaHangGuiThuGom()
                );
            }

            $json_body2 = json_encode($body_content2);
            $client2 = new Client();
            $adapter2 = new Client\Adapter\Curl();
            $adapter2->setOptions(array(
                'curloptions' => array(
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false
                )
            ));
            $client2->setAdapter($adapter2);
            $client2->setOptions(array(
                'maxredirects' => 0,
                'timeout' => 30
            ));
            $client2->setMethod('POST');
            if ($LGSP_VNPOST_VTU_CONNECT == '1') {//VNPOST VTU
                $LGSP_VNPOST_ADAPTER_URL_API = $LGSP_VNPOST_ADAPTER_URL_API . '?serviceType=VNPost&serviceName=OrderCancel';
                $client2->setHeaders(array(
                    'Authorization' => 'Bearer ' . $token_access,
                    'Content-Type' => 'application/json'
                ));
            } else {//VNPOST LCI
                $client2->setHeaders(array(
                    'Authorization' => 'Bearer ' . $token_access,
                    'Content-Type' => 'application/json',
                    'service-code' => 'vnpost_cancel_order'));
            }

            $client2->setRawBody($json_body2);
            $client2->setUri($LGSP_VNPOST_ADAPTER_URL_API);
            $response2 = $client2->send();
            if ($response2->isSuccess()) {

                $data2 = json_decode($response2->getBody(), true);
                $this->INSERT_LOG('[VNPOST][HO_SO_ONLINE][' . $this->getSoHoSo() . ']XOA YEU CAU DON HANG HS VNPOST LCI THANH CONG!'
                            . 'Thong tin API : ' . json_encode($body_content2)
                            . ', Ket qua tra ve : Status=>' . $data2["Status"]
                            . ' va Message=>' . $data2["Message"], 0);
            } else {
                $this->INSERT_LOG('[VNPOST][HO_SO_ONLINE][' . $this->getSoHoSo() . ']XOA YEU CAU DON HANG HS VNPOST LCI THAT BAI!'
                            . 'Thong tin API : ' . json_encode($body_content2)
                            . ', Ket qua tra ve : ' . json_encode($response2->getBody(), true), 0);
                return false;
            }

        }else{//VNPOST BT
            if ($vnpostYeuCau->daGuiSangBuuDien($user)) {
                if (!$vnpostYeuCau->huyGuiSangBuuDien($user)) {
                    return false;
                }
            }
        }

        $package = new Package\VNPOST();
        $rs = $package->HUY_YEU_CAU_THU_GOM([
            'P_MA_HO_SO_ONLINE' => $maHoSo
        ]);
        if ($rs === 1) {
            $this->setMaHangGuiThuGom(null);
            return true;
        }
        return false;
    }

    /**
     * Dong bo hoa du lieu voi ho so tiep nhan ben trong
     *
     * @return boolean
     */
    public function syncWithHoSo() {
        $package = new Package\DVC_HS();
        $result = $package->SYNC_WITH_HO_SO([
            'P_MA_HO_SO_ONLINE' => $this->getMaHoSo()
        ]);
        return $result === 1;
    }

    public function updateTrangThaiHoSo($trangThaiHoSo) {
        $rs = (new Package\DVC_HS())->UPDATE_TRANG_THAI_HO_SO([
            'P_MA_HO_SO' => $this->getMaHoSo(),
            'P_TRANG_THAI_HO_SO' => $trangThaiHoSo
        ]);
        if ($rs) {
            $this->setTrangThaiHoSo($trangThaiHoSo);
            return true;
        }
        return false;
    }

    public function updateThongTinChung() {
        $f = new OracleFunction\UPDATE_HSONLINE_THONGTINCHUNG([
            'P_MA_HO_SO' => $this->getMaHoSo(),
            'P_MA_PHUONG_XA_NOP' => $this->getPhuongXaNop()->getMaPhuongXa(),
            'P_MA_QUAN_HUYEN_NOP' => $this->getQuanHuyenNop()->getMaQuanHuyen(),
            'P_MA_QTTT' => $this->getQttt()->getMaQttt(),
            'P_TRANG_THAI_HO_SO' => $this->getTrangThaiHoSo(),
            'P_GIAY_TO_KHAC' => $this->getGiayToKhac(),
            'P_GHI_CHU' => $this->getGhiChu(),
            'P_MA_BIEU_MAU_NGUOI_NOP' => $this->getMaBieuMauNguoiNop(),
            'P_MA_HINH_THUC_THANH_TOAN' => $this->getMaHinhThucThanhToan(),
            'P_FILE_GIAY_TO_KHAC' => $this->getFileGiayToKhac(),
            'P_SO_GIAY_CHUNG_NHAN' => $this->getSoGCNGP(),
            'P_NGAY_CAP_GCNGP' => $this->getNgayCapGCNGP(),
            'P_NOI_CAP_GCNGP' => $this->getNoiCapGCNGP(),
            'P_MA_TINH_CAP_CMND' => $this->getMaTinhCapCMND(),
            'P_MA_DM_QUOC_GIA' => $this->getCongDan()->getMaDMQuocGia(),
            'P_MA_DM_DIA_CHI' => $this->getCongDan()->getMaDMDiaChi(),
            'P_DIA_CHI_CHI_TIET' => $this->getCongDan()->getDiaChiNuocNgoai(),
            'P_VE_VIEC' => $this->getVeViec(),
            'P_SO_HOA_DON_THANH_TOAN' => parent::getSoHoaDonThanhToan(),
            'P_FILE_DA_THANH_TOAN' => $this->getFileDaThanhToan(),
            'P_CHO_PHEP_CN_NGAY_NOP' => (int) Entity\System\Parameter::fromId('CAP_NHAT_NGAY_NOP_SAU_CUNG')->getValue()==1?1:null
        ]);
        if ($f->getResult() === 1) {
            $this->setMaHoSo($f->getInt('P_MA_HO_SO'));
            $this->setSoHoSo($f->getString('P_SO_HO_SO'));
            $this->setKhoaCapNhat($f->getString('P_KHOA_CAP_NHAT'));
            $this->updateHoSoNopTuCongDVCQG($this->getMaHoSo());
            return true;
        }
        return false;
    }

    public function updateHoSoNopTuCongDVCQG($maHoSo){
        if (\Session::_isset(TIEP_DAU_NGU_SESSION . 'dvcqgNopHoSo')) {
            $P_HS_NOP_TU_CDVCQG = '1';
            \Session::_unset(TIEP_DAU_NGU_SESSION . 'dvcqgNopHoSo');
        } else {
            $P_HS_NOP_TU_CDVCQG = '0';
        }
        $f2 = new \Oracle\OracleFunction\UPDATE_HS_NOP_TU_CDVCQG([
            'P_MA_HO_SO' => $maHoSo,
            'P_HS_NOP_TU_CDVCQG' => $P_HS_NOP_TU_CDVCQG
        ]);
    }

    public function updateBieuMauThuTuc() {
        $f = new OracleFunction\UPDATE_HSONLINE_TTBIEUMAU([
            'P_MA_HO_SO' => $this->getMaHoSo(),
            'P_MA_BIEU_MAU' => $this->getMaBieuMau(),
            'P_DU_LIEU_BIEU_MAU' => $this->getDuLieuBieuMau()
        ]);
        if ($f->getResult() === 1) {
            (new \Model\HoSo\BieuMau\DuLieu())->updateFromBieuMau([
                'P_MA_HO_SO_TAM' => $this->getMaHoSo(),
                'P_MA_BIEU_MAU' => $this->getMaBieuMau(),
                'P_DU_LIEU_BIEU_MAU' => $this->getDuLieuBieuMau()
            ], 0);
            return true;
        }
        return false;
    }

    public function updateThongTinNguoiNop() {
        $congDan = $this->getCongDan();
        $ngayCapCmnd = $congDan->getNgayCapCmnd();
        return (new Package\DVC_HS())->UPDATE_THONG_TIN_NGUOI_NOP([
            'P_MA_HO_SO' => $this->getMaHoSo(),
            'P_CMND_CONG_DAN' => $congDan->getSoCmnd(),
            'P_TEN_CONG_DAN' => $congDan->getTenCongDan(),
            'P_NGAY_CAP' => $ngayCapCmnd ? $ngayCapCmnd->format('d/m/Y') : null,
            'P_NOI_CAP' => $congDan->getNoiCapCmnd(),
            'P_DIA_CHI' => $congDan->getDiaChi(),
            'P_DI_DONG' => $congDan->getDiDong(),
            'P_MA_CONG_DAN' => $congDan->getMaCongDan(),
            'P_EMAIL' => $congDan->getEmail(),
            'P_MA_PHUONG_XA' => $congDan->getMaPhuongXa(),
            'P_FAX' => $congDan->getFax(),
            'P_WEBSITE' => $congDan->getWebsite(),
            'P_TEN_CO_QUAN_TO_CHUC' => $congDan->getTenCoQuanToChuc(),
            'P_SO_GIAY_CHUNG_NHAN' => $this->getSoGCNGP(),
            'P_GIOI_TINH_CONG_DAN' => $congDan->getGioiTinh(),
            'P_NGAY_SINH_CONG_DAN' => $congDan->getNgaySinh(),
            'P_DAN_TOC_CONG_DAN' => $congDan->getDanToc()
        ]);
    }

    public function updateHinhThucNop() {
        return (new Package\DVC_HS())->UPDATE_HINH_THUC_NOP([
            'P_MA_HO_SO' => $this->getMaHoSo(),
            'P_MA_HINH_THUC_NOP' => $this->getMaHinhThucNop(),
            'P_MA_PHUONG_XA_THU_GOM' => $this->getMaPhuongXaThuGom(),
            'P_DIA_CHI_THU_GOM' => $this->getDiaChiThuGom(),
            'P_NGAY_YEU_CAU_THU_GOM' => $this->getNgayYeuCauThuGom()
        ]);
    }

    public function updateHinhThucNhanKetQua() {
        return (new Package\DVC_HS())->UPDATE_NOI_NHAN_HO_SO([
            'P_MA_HO_SO' => $this->getMaHoSo(),
            'P_HINH_THUC_NHAN_KET_QUA' => $this->getMaHinhThucNhanKetQua(),
            'P_MA_PHUONG_XA' => $this->getMaPhuongXaNhanKetQua(),
            'P_DIA_CHI' => $this->getDiaChiNhanKetQua()
        ]);
    }

    public function updateLePhiHoSoPack1() {
        foreach ($this->getDmLePhiHoSo()->getItems() as $item) {
            $item->setMaHoSoOnline($this->getMaHoSo());
            if (!$item->update()) {
                return false;
            }
        }
        return $this->getDmLePhiHoSo()->deleteMissingItems();
    }

    /**
     * @method updatePack1 bao gom cac goi thong tin:
     * + Thong tin chung
     * + Thong tin nguoi nop
     * + Thanh phan ho so
     * + Hinh thuc nop
     * + Hinh thuc nhan ket qua
     * + Le phi ho so
     * @return  ResultInfo code and message
     */
    public function updatePack1() {
        if (!$this->updateThongTinChung()) {
            return new ResultInfo(0, 'Không thể cập nhật thông tin chung');
        }
        if (!$this->updateBieuMauThuTuc()) {
            return new ResultInfo(0, 'Không thể cập nhật thông tin cung cấp thêm');
        }
        if (!$this->updateThongTinNguoiNop()) {
            return new ResultInfo(0, 'Không thể cập nhật thông tin người nộp');
        }
        if (!$this->updateThanhPhanHoSo()) {
            return new ResultInfo(0, 'Không thể cập nhật thành phần hồ sơ');
        }
        if (!$this->updateHinhThucNop()) {
            return new ResultInfo(0, 'Không thể cập nhật hình thức nộp');
        }
        if (!$this->updateHinhThucNhanKetQua()) {
            return new ResultInfo(0, 'Không thể cập nhật hình thức nhận kết quả');
        }
        if (!$this->updateLePhiHoSoPack1()) {
            return new ResultInfo(0, 'Không thể cập nhật lệ phí hồ sơ');
        }

        return $this->hoanTatCapNhat();
    }

    public function hoanTatCapNhat() {
        if ($this->coYeuCauThuGom()) {

            $LGSP_VNPOST_ADAPTER_URL_API = Model\Entity\System\Parameter::fromId('LGSP_VNPOST_ADAPTER_URL_API')->getValue();
            $LGSP_NGSP_API_CONNECT_VNPOST = Model\Entity\System\Parameter::fromId('LGSP_NGSP_API_CONNECT_VNPOST')->getValueArray();
            if (!empty($LGSP_NGSP_API_CONNECT_VNPOST['API_POST_ORDER'])) {
                if (!$this->guiYeuCauThuGom_VNPOSTBTE()) {
                    return new ResultInfo(0, 'Yêu cầu thu gom qua NGSP không thể thực hiện');
                }
            } elseif (!empty($LGSP_VNPOST_ADAPTER_URL_API)) {
                if (!$this->guiYeuCauThuGom_VNPOST()) {
                    return new ResultInfo(0, 'Yêu cầu thu gom qua LGSP không thể thực hiện');
                }
            } else {
                if (!$this->guiYeuCauThuGom()) {
                    return new ResultInfo(0, 'Yêu cầu thu gom không thể thực hiện');
                }
            }

        } elseif ($this->daYeuCauThuGom()) {
            if (!$this->huyYeuCauThuGom()) {
                return new ResultInfo(0, 'Không thể hủy yêu cầu thu gom');
            }
        }
        if ($this->getMaHoSoTiepNhan()) {
            if (!$this->syncWithHoSo()) {
                return new ResultInfo(0, 'Không thể gửi thông tin hồ sơ đến cơ quan giải quyết');
            }
        }
        if ((int) Entity\System\Parameter::fromId('CHECK_TRUNG_SO_HO_SO')->getValue() == 1) {
            if ($this->checkTonTaiSHS($this->getSoHoSo(), '', $this->getMaHoSo()) == 1) {
                return new ResultInfo(0, 'Có vấn đề trong quá trình nhập dữ liệu của quý khách. Đề nghị tiến hành lại phần nhập hồ sơ để hạn chế sơ xuất!');
            }
        }
        return new ResultInfo(1);
    }

    public function checkTonTaiSHS ($P_SO_HO_SO, $P_MA_HO_SO, $P_MA_HO_SO_ONLINE) {
        $rows = new OracleFunction\CHECK_TRUNG_SO_HO_SO(Array(
            'P_SO_HO_SO' => $P_SO_HO_SO,
            'P_MA_HO_SO' => $P_MA_HO_SO,
            'P_MA_HO_SO_ONLINE' => $P_MA_HO_SO_ONLINE
        ));
        return $rows->getResult();
    }

    public function guiThongBaoDaNop() {
        if ((int) Entity\System\Parameter::fromId('dvc_gui_sms_tb_da_nop')->getValue() === 1) {
            $this->guiSMSDaNop();
        }
        if ((int) Entity\System\Parameter::fromId('dvc_gui_mail_tb_da_nop')->getValue() === 1) {
            $this->guiEmailDaNop();
        }
        // $this->guiEmailThongBaoTraCuuHS();
    }

    public function guiEmailDaNop() {
        $url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
        $trangThai = '';
        if ($this->getTrangThaiHoSo() == 'LUU_NHUNG_KHONG_NOP') {
            $trangThai = 'Hồ sơ được lưu nhưng chưa nộp';
        } elseif ($this->getTrangThaiHoSo() == 'DA_NOP') {
            $trangThai = 'Hồ sơ đã được nộp online';
        } elseif ($this->getTrangThaiHoSo() == 'DANG_CAP_NHAT') {
            $trangThai = 'Hồ sơ đang cập nhật thông tin';
        } elseif ($this->getTrangThaiHoSo() == 'DANG_TIEP_NHAN') {
            $trangThai = 'Hồ sơ đang tiếp nhận';
        } elseif ($this->getTrangThaiHoSo() == 'DA_TIEP_NHAN') {
            $trangThai = 'Hồ sơ đã được tiếp nhận';
        } elseif ($this->getTrangThaiHoSo() == 'DA_HUY') {
            $trangThai = 'Hồ sơ đã hủy';
        } elseif ($this->getTrangThaiHoSo() == 'CHO_BO_SUNG') {
            $trangThai = 'Hồ sơ đang chờ bổ sung';
        } elseif ($this->getTrangThaiHoSo() == 'DA_BO_SUNG_VA_NOP_LAI') {
            $trangThai = 'Hồ sơ đã bổ sung và nộp lại';
        } elseif ($this->getTrangThaiHoSo() == 'CONG_DAN_XOA') {
            $trangThai = 'Hồ sơ đã bị xóa';
        } elseif ($this->getTrangThaiHoSo() == 'DANG_TAM_DUNG') {
            $trangThai = 'Hồ sơ đang tạm dừng';
        }
        // Nhin 8661 Cấu hình gửi thông báo cho công dân theo quy trình thủ tục
        $maQTTT = $this->getMaQttt();
        $cauHinhMail = (new StoreProcedure\SELECT_CONTENT_MAIL_QTTT([
            'P_MA_QTTT' => $maQTTT,
            'P_TYPE' => 1 //Nộp hồ sơ online
        ]))->getDefaultResult(0);

        $body = new Mime\Message();
        $email = $this->getEmailCongDan();
        if (empty($email) && ($congDan = $this->getCongDan())) {
            $email = $congDan->getEmail();
        }
        if ($email) {
            $noiDungMail = '';
            $noiDungMail = Entity\System\Parameter::fromId('mail_template_hsol_danop')->getValue();
            if ($noiDungMail != '') {//Biến template có giá trị
                //replace các biến HS1 và HS6 về lần lượt là số hồ sơ và tên người nộp
                $noiDungMail = str_replace('${HS6}',$congDan->getTenCongDan(),$noiDungMail);
                $noiDungMail = str_replace('${HS1}',$this->getSoHoSo(),$noiDungMail);
                $noiDungSMS = str_replace('${HS80}',$mangDuLieuHoSo['HS80'],$noiDungSMS);
            } else {
                $noiDungMail = '';
                if (($congDan = $this->getCongDan()) && ($email = $congDan->getEmail()) && ($donViTiepNhan = $this->getDonViTiepNhan())) {
                    if(!empty($cauHinhMail['CONTENT'])){ // Nhin 10556 Nâng cấp thêm thông tin vào nội dung mail
                        $noiDungMail = html_entity_decode($cauHinhMail['CONTENT']);
                        $noiDungMail = $this->replaceVariableHS($noiDungMail);
                    }else{
                        $pck = new \Oracle\Package\MC_TT();
                        $data = array('P_SO_HO_SO' => $this->getSoHoSo());
                        $ndMailCH = $pck->SELECT_TB_GUI_MAIL($data)->P_CUR;
                        $noiDungMail = '<html><head></head><body>';
                        $noiDungMail .= sprintf('<p style=margin-bottom:25px>Xin chào %s,</p>', $congDan->getTenCongDan());
                        $noiDungMail .= '<p>Hệ thống Cổng Dịch vụ công xin thông báo:</p>';
                        $noiDungMail .= sprintf('<p>Bạn đã nộp hồ sơ <strong>%s</strong> đến đơn vị: <strong>%s</strong></p>', $this->getSoHoSo(), $donViTiepNhan->getTenDonVi());
                        if ($tenDangNhap = $congDan->getTenDangNhap()) {
                            $noiDungMail .= sprintf('<p>Tài khoản nộp: <strong>%s</strong></p>', $tenDangNhap);
                        } else {
                            $noiDungMail .= sprintf('<p>Khóa cập nhật hồ sơ: <strong>%s</strong></p>', $this->getKhoaCapNhat());
                        }
                        $noiDungMail .= sprintf('<p><strong>Thông tin hồ sơ</strong></p>');
                        $noiDungMail .= sprintf('<p style=margin-bottom:25px>Số hồ sơ: %s</p>', $this->getSoHoSo());
                        $noiDungMail .= sprintf('<p style=margin-bottom:25px>Người đại diện: %s</p>', $congDan->getTenCongDan());
                        $noiDungMail .= sprintf('<p style=margin-bottom:25px>Tên lĩnh vực: %s</p>', $this->getThuTuc()->getLinhVuc()->getTenLinhVuc());
                        $noiDungMail .= sprintf('<p style=margin-bottom:25px>Tên thủ tục: %s</p>', $this->getThuTuc()->getTenThuTuc());
                        $noiDungMail .= sprintf('<p style=margin-bottom:25px>Tên cơ quan: %s</p>', $this->getCoQuan()->getTenCoQuan());
                        $noiDungMail .= sprintf('<p style=margin-bottom:25px>Hình thức nhận kết quả: %s</p>', $this->getHinhThucNhanKetQua()->getTenHinhThuc());
                        // $noiDungMail .= sprintf('<p style=margin-bottom:25px>Ngày tiếp nhận: %s</p>', $this->getNgayTiepNhan('d/m/Y H:i:s'));
                        // $noiDungMail .= sprintf('<p style=margin-bottom:25px>Ngày hẹn trả: %s</p>', $this->getNgayHenTra('d/m/Y H:i:s'));
                        $noiDungMail .= sprintf('<p style=margin-bottom:25px>Trạng Thái hồ sơ: %s</p>', $trangThai);

                        // hiển thị nội dung gửi mail đã đc cấu hình trong thủ tục
                        // Sửa lỗi font chữ
                        $search = array('&#39;','&nbsp;','&iexcl;','&cent;','&pound;','&curren;','&yen;','&#x20B9;','&brvbar;','&sect;','&uml;','&copy;','&ordf;','&laquo;','&not;','&shy;','&reg;','&macr;','&deg;','&plusmn;','&sup2;','&sup3;','&acute;','&micro;','&para;','&middot;','&cedil;','&sup1;','&ordm;','&raquo;','&frac14;','&frac12;','&frac34;','&iquest;','&Agrave;','&Aacute;','&Acirc;','&Atilde;','&Auml;','&Aring;','&AElig;','&Ccedil;','&Egrave;','&Eacute;','&Ecirc;','&Euml;','&Igrave;','&Iacute;','&Icirc;','&Iuml;','&ETH;','&Ntilde;','&Ograve;','&Oacute;','&Ocirc;','&Otilde;','&Ouml;','&times;','&Oslash;','&Ugrave;','&Uacute;','&Ucirc;','&Uuml;','&Yacute;','&THORN;','&szlig;','&agrave;','&aacute;','&acirc;','&atilde;','&auml;','&aring;','&aelig;','&ccedil;','&egrave;','&eacute;','&ecirc;','&euml;','&igrave;','&iacute;','&icirc;','&iuml;','&eth;','&ntilde;','&ograve;','&oacute;','&ocirc;','&otilde;','&ouml;','&divide;','&oslash;','&ugrave;','&uacute;','&ucirc;','&uuml;','&yacute;','&thorn;','&yuml;');
                        $replace = array("'",' ','¡','¢','£','¤','¥','₹','¦','§','¨','©','ª','«','¬','','®','¯','°','±','²','³','´','µ','¶','·','¸','¹','º','»','¼','½','¾','¿','À','Á','Â','Ã','Ä','Å','Æ','Ç','È','É','Ê','Ë','Ì','Í','Î','Ï','Ð','Ñ','Ò','Ó','Ô','Õ','Ö','×','Ø','Ù','Ú','Û','Ü','Ý','Þ','ß','à','á','â','ã','ä','å','æ','ç','è','é','ê','ë','ì','í','î','ï','ð','ñ','ò','ó','ô','õ','ö','÷','ø','ù','ú','û','ü','ý','þ','ÿ');
                        $noiDungMail .= sprintf('<p>%s</p>', str_replace($search,$replace,html_entity_decode($ndMailCH)));

                        $noiDungMail .= '<p>Để theo dõi đươc kết quả giải quyết hồ sơ. Ông/Bà vui lòng tra cứu hồ sơ tại địa chỉ dưới dây: </p>';
                        $noiDungMail .= '<a href = "https://www.youtube.com/watch?v=1O94PgfcM5U">https://www.youtube.com/watch?v=1O94PgfcM5U</a>';
                        $noiDungMail .= sprintf('<p style=margin-bottom:25px>Địa chỉ tra cứu: <a href = "%s">%s</a></p>', $url, $url);

                        $noiDungMail .= sprintf('<p style=margin-top:25px>Trân trọng.</p>');
                        $noiDungMail .= '</body></html>';
                    }
                }elseif ($this->getEmailCongDan() != '' && !empty($this->getEmailCongDan())) {
                    $noiDungMail = '<html><head></head><body>';
                    $noiDungMail .= sprintf('<p style=margin-bottom:25px><b>Xin chào ông/bà %s,</b></p>', $this->getTenCongDanNop());
                    $noiDungMail .= sprintf('<p style=margin-bottom:25px>Số hồ sơ: %s</p>', $this->getSoHoSo());
                    $noiDungMail .= sprintf('<p style=margin-bottom:25px>Người đại diện: %s</p>', $this->getTenCongDanNop());
                    $noiDungMail .= sprintf('<p style=margin-bottom:25px>Tên lĩnh vực: %s</p>', $this->getThuTuc()->getLinhVuc()->getTenLinhVuc());
                    $noiDungMail .= sprintf('<p style=margin-bottom:25px>Tên thủ tục: %s</p>', $this->getThuTuc()->getTenThuTuc());
                    $noiDungMail .= sprintf('<p style=margin-bottom:25px>Tên cơ quan: %s</p>', $this->getCoQuan()->getTenCoQuan());
                    $noiDungMail .= sprintf('<p style=margin-bottom:25px>Hình thức nhận kết quả: %s</p>', $this->getHinhThucNhanKetQua()->getTenHinhThuc());
                    // $noiDungMail .= sprintf('<p style=margin-bottom:25px>Ngày tiếp nhận: %s</p>', $this->getNgayTiepNhan('d/m/Y H:i:s'));
                    // $noiDungMail .= sprintf('<p style=margin-bottom:25px>Ngày hẹn trả: %s</p>', $this->getNgayHenTra('d/m/Y H:i:s'));
                    $noiDungMail .= sprintf('<p style=margin-bottom:25px>Trạng Thái hồ sơ: %s</p>', $trangThai);
                    $noiDungMail .= '<p>Để theo dõi đươc kết quả giải quyết hồ sơ. Ông/Bà vui lòng tra cứu hồ sơ tại địa chỉ dưới dây: </p>';
                    $noiDungMail .= '<a href = "https://www.youtube.com/watch?v=1O94PgfcM5U">https://www.youtube.com/watch?v=1O94PgfcM5U</a>';
                    $noiDungMail .= sprintf('<p style=margin-bottom:25px>Địa chỉ tra cứu: <a href = "%s">%s</a></p>', $url, $url);
                    $noiDungMail .= sprintf('<p style=margin-top:25px>Trân trọng!</p>');
                    $noiDungMail .= '</body></html>';
                }
                else{
                    $noiDungMail = '<html><head></head><body>';
                    $noiDungMail .= sprintf('<p style=margin-bottom:25px><b>Xin chào ông/bà lalalalala %s,</b></p>', $this->getTenCongDanNop());
                    $noiDungMail .= sprintf('<p style=margin-bottom:25px>Số hồ sơ: %s</p>', $this->getSoHoSo());
                    $noiDungMail .= sprintf('<p style=margin-bottom:25px>Người đại diện: %s</p>', $this->getTenCongDanNop());
                    $noiDungMail .= sprintf('<p style=margin-bottom:25px>Tên lĩnh vực: %s</p>', $this->getThuTuc()->getLinhVuc()->getTenLinhVuc());
                    $noiDungMail .= sprintf('<p style=margin-bottom:25px>Tên thủ tục: %s</p>', $this->getThuTuc()->getTenThuTuc());
                    $noiDungMail .= sprintf('<p style=margin-bottom:25px>Tên cơ quan: %s</p>', $this->getCoQuan()->getTenCoQuan());
                    $noiDungMail .= sprintf('<p style=margin-bottom:25px>Hình thức nhận kết quả: %s</p>', $this->getHinhThucNhanKetQua()->getTenHinhThuc());
                    // $noiDungMail .= sprintf('<p style=margin-bottom:25px>Ngày tiếp nhận: %s</p>', $this->getNgayTiepNhan('d/m/Y H:i:s'));
                    // $noiDungMail .= sprintf('<p style=margin-bottom:25px>Ngày hẹn trả: %s</p>', $this->getNgayHenTra('d/m/Y H:i:s'));
                    $noiDungMail .= sprintf('<p style=margin-bottom:25px>Trạng Thái hồ sơ: %s</p>', $trangThai);
                    $noiDungMail .= '<p>Để theo dõi đươc kết quả giải quyết hồ sơ. Ông/Bà vui lòng tra cứu hồ sơ tại địa chỉ dưới dây: </p>';
                    $noiDungMail .= '<a href = "https://www.youtube.com/watch?v=1O94PgfcM5U">https://www.youtube.com/watch?v=1O94PgfcM5U</a>';
                    $noiDungMail .= sprintf('<p style=margin-bottom:25px>Địa chỉ tra cứu: <a href = "%s">%s</a></p>', $url, $url);
                    $noiDungMail .= sprintf('<p style=margin-top:25px>Trân trọng!</p>');
                    $noiDungMail .= '</body></html>';
                }
            }
            //gui mail
            $part = new Mime\Part($noiDungMail);
            $part->setType(Mime\Mime::TYPE_HTML);
            $body->addPart($part);
            $message = new Mail\Message();
            $message->addFrom(Sys\Parameter::fromId('mail_username')->getValue());
            $message->addTo($email);
            // Nhin 8661 Cấu hình gửi thông báo cho công dân theo quy trình thủ tục
            if(!empty($cauHinhMail['TITLE'])){
                $message->setSubject(sprintf('%s',mb_strimwidth($cauHinhMail['TITLE'], 0, 69, "...")));
            }else{
                $message->setSubject(sprintf('Cổng Dịch vụ công - Thông báo đã nộp hồ sơ %s', $this->getSoHoSo()));
            }
            $message->setBody($body);
            $logMail = new \Oracle\OracleFunction\INSERT_LOG_THONG_BAO(array(
                'P_MA_HO_SO_ONLINE' => $this->getMaHoSo() ?: '',
                'P_MA_CAN_BO_THUC_HIEN' => '',
                'P_MA_THAO_TAC' => 5,
                'P_NOI_DUNG' => $noiDungMail ?: ''
            ));
            $headers = $message->getHeaders();
            $headers->removeHeader('Content-Type');
            $headers->addHeaderLine('Content-Type', 'text/html; charset=UTF-8');
            return System::getDefaultSmtpTranport()->send($message);
        }



//        if (($congDan = $this->getCongDan()) && ($email = $congDan->getEmail()) && ($donViTiepNhan = $this->getDonViTiepNhan())) {
//            $body = new Mime\Message();
//            $pck = new \Oracle\Package\MC_TT();
//            $data = array('P_SO_HO_SO' => $this->getSoHoSo());
//            $ndMailCH = $pck->SELECT_TB_GUI_MAIL($data)->P_CUR;
//            $noiDungSMS = '<html><head></head><body>';
//            $noiDungSMS .= sprintf('<p style=margin-bottom:25px>Xin chào %s,</p>', $congDan->getTenCongDan());
//            $noiDungSMS .= '<p>Hệ thống Một cửa điện tử liên thông - VNPT iGate xin thông báo:</p>';
//            $noiDungSMS .= sprintf('<p>Bạn đã nộp hồ sơ <strong>%s</strong> đến đơn vị: <strong>%s</strong></p>', $this->getSoHoSo(), $donViTiepNhan->getTenDonVi());
//            if ($tenDangNhap = $congDan->getTenDangNhap()) {
//                $noiDungSMS .= sprintf('<p>Tài khoản nộp: <strong>%s</strong></p>', $tenDangNhap);
//            } else {
//                $noiDungSMS .= sprintf('<p>Khóa cập nhật hồ sơ: <strong>%s</strong></p>', $this->getKhoaCapNhat());
//            }
//            $noiDungSMS .= sprintf('<p><strong>Thông tin hồ sơ</strong></p>');
//            $noiDungSMS .= sprintf('<p style=margin-bottom:25px>Số hồ sơ: %s</p>', $this->getSoHoSo());
//            $noiDungSMS .= sprintf('<p style=margin-bottom:25px>Người đại diện: %s</p>', $congDan->getTenCongDan());
//            $noiDungSMS .= sprintf('<p style=margin-bottom:25px>Tên lĩnh vực: %s</p>', $this->getThuTuc()->getLinhVuc()->getTenLinhVuc());
//            $noiDungSMS .= sprintf('<p style=margin-bottom:25px>Tên thủ tục: %s</p>', $this->getThuTuc()->getTenThuTuc());
//            $noiDungSMS .= sprintf('<p style=margin-bottom:25px>Tên cơ quan: %s</p>', $this->getCoQuan()->getTenCoQuan());
//            $noiDungSMS .= sprintf('<p style=margin-bottom:25px>Hình thức nhận kết quả: %s</p>', $this->getHinhThucNhanKetQua()->getTenHinhThuc());
//            // $noiDungSMS .= sprintf('<p style=margin-bottom:25px>Ngày tiếp nhận: %s</p>', $this->getNgayTiepNhan('d/m/Y H:i:s'));
//            // $noiDungSMS .= sprintf('<p style=margin-bottom:25px>Ngày hẹn trả: %s</p>', $this->getNgayHenTra('d/m/Y H:i:s'));
//            $noiDungSMS .= sprintf('<p style=margin-bottom:25px>Trạng Thái hồ sơ: %s</p>', $trangThai);
//
//            // hiển thị nội dung gửi mail đã đc cấu hình trong thủ tục
//            $noiDungSMS .= sprintf('<p>%s</p>',$ndMailCH);
//
//            $noiDungSMS .= '<p>Để theo dõi được kết quả giải quyết hồ sơ online Ông/Bà vui lòng xem hướng dẫn tra cứu tại địa chỉ: </p>';
//            $noiDungSMS .= '<a href = "https://www.youtube.com/watch?v=1O94PgfcM5U">https://www.youtube.com/watch?v=1O94PgfcM5U</a>';
//            $noiDungSMS .= sprintf('<p style=margin-bottom:25px>Địa chỉ tra cứu: <a href = "%s">%s</a></p>', $url, $url);
//
//            $noiDungSMS .= sprintf('<p style=margin-top:25px>Trân trọng.</p>');
//            $noiDungSMS .= '</body></html>';
//
//            $part = new Mime\Part($noiDungSMS);
//            $part->setType(Mime\Mime::TYPE_HTML);
//            $body->addPart($part);
//            $message = new Mail\Message();
//            $message->addFrom(Sys\Parameter::fromId('mail_username')->getValue());
//            $message->addTo($email);
//            $message->setSubject(sprintf('VNPT iGate - Thông báo đã nộp hồ sơ %s', $this->getSoHoSo()));
//            $message->setBody($body);
//            return System::getDefaultSmtpTranport()->send($message);
//        }
//        elseif ($this->getEmailCongDan() != '' && !empty($this->getEmailCongDan())) {
//            $body = new Mime\Message();
//            $noiDungSMS = '<html><head></head><body>';
//            $noiDungSMS .= sprintf('<p style=margin-bottom:25px><b>Xin chào ông/bà %s,</b></p>', $this->getTenCongDanNop());
//            $noiDungSMS .= sprintf('<p style=margin-bottom:25px>Số hồ sơ: %s</p>', $this->getSoHoSo());
//            $noiDungSMS .= sprintf('<p style=margin-bottom:25px>Người đại diện: %s</p>', $this->getTenCongDanNop());
//            $noiDungSMS .= sprintf('<p style=margin-bottom:25px>Tên lĩnh vực: %s</p>', $this->getThuTuc()->getLinhVuc()->getTenLinhVuc());
//            $noiDungSMS .= sprintf('<p style=margin-bottom:25px>Tên thủ tục: %s</p>', $this->getThuTuc()->getTenThuTuc());
//            $noiDungSMS .= sprintf('<p style=margin-bottom:25px>Tên cơ quan: %s</p>', $this->getCoQuan()->getTenCoQuan());
//            $noiDungSMS .= sprintf('<p style=margin-bottom:25px>Hình thức nhận kết quả: %s</p>', $this->getHinhThucNhanKetQua()->getTenHinhThuc());
//            // $noiDungSMS .= sprintf('<p style=margin-bottom:25px>Ngày tiếp nhận: %s</p>', $this->getNgayTiepNhan('d/m/Y H:i:s'));
//            // $noiDungSMS .= sprintf('<p style=margin-bottom:25px>Ngày hẹn trả: %s</p>', $this->getNgayHenTra('d/m/Y H:i:s'));
//            $noiDungSMS .= sprintf('<p style=margin-bottom:25px>Trạng Thái hồ sơ: %s</p>', $trangThai);
//            $noiDungSMS .= '<p>Để theo dõi được kết quả giải quyết hồ sơ online Ông/Bà vui lòng xem hướng dẫn tra cứu tại địa chỉ: </p>';
//            $noiDungSMS .= '<a href = "https://www.youtube.com/watch?v=1O94PgfcM5U">https://www.youtube.com/watch?v=1O94PgfcM5U</a>';
//            $noiDungSMS .= sprintf('<p style=margin-bottom:25px>Địa chỉ tra cứu: <a href = "%s">%s</a></p>', $url, $url);
//            $noiDungSMS .= sprintf('<p style=margin-top:25px>Trân trọng!</p>');
//            $noiDungSMS .= '</body></html>';
//            $part = new Mime\Part($noiDungSMS);
//            $part->setType(Mime\Mime::TYPE_HTML);
//            $body->addPart($part);
//            $message = new Mail\Message();
//            $message->addFrom(Sys\Parameter::fromId('mail_username')->getValue());
//            $message->addTo($email);
//            $message->setSubject(sprintf('VNPT iGate - Thông báo đã nộp hồ sơ %s', $this->getSoHoSo()));
//            $message->setBody($body);
//            return System::getDefaultSmtpTranport()->send($message);
//        }
    }

    // public function guiEmailThongBaoTraCuuHS () {
    //     $url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
    //     $trangThai = '';
    //     if ($this->getTrangThaiHoSo() == 'LUU_NHUNG_KHONG_NOP') {
    //         $trangThai = 'Hồ sơ được lưu nhưng chưa nộp';
    //     } elseif ($this->getTrangThaiHoSo() == 'DA_NOP') {
    //         $trangThai = 'Hồ sơ đã được nộp online';
    //     } elseif ($this->getTrangThaiHoSo() == 'DANG_CAP_NHAT') {
    //         $trangThai = 'Hồ sơ đang cập nhật thông tin';
    //     } elseif ($this->getTrangThaiHoSo() == 'DANG_TIEP_NHAN') {
    //         $trangThai = 'Hồ sơ đang tiếp nhận';
    //     } elseif ($this->getTrangThaiHoSo() == 'DA_TIEP_NHAN') {
    //         $trangThai = 'Hồ sơ đã được tiếp nhận';
    //     } elseif ($this->getTrangThaiHoSo() == 'DA_HUY') {
    //         $trangThai = 'Hồ sơ đã hủy';
    //     } elseif ($this->getTrangThaiHoSo() == 'CHO_BO_SUNG') {
    //         $trangThai = 'Hồ sơ đang chờ bổ sung';
    //     } elseif ($this->getTrangThaiHoSo() == 'DA_BO_SUNG_VA_NOP_LAI') {
    //         $trangThai = 'Hồ sơ đã bổ sung và nộp lại';
    //     } elseif ($this->getTrangThaiHoSo() == 'CONG_DAN_XOA') {
    //         $trangThai = 'Hồ sơ đã bị xóa';
    //     } elseif ($this->getTrangThaiHoSo() == 'DANG_TAM_DUNG') {
    //         $trangThai = 'Hồ sơ đang tạm dừng';
    //     }
    //     if (($congDan = $this->getCongDan()) && ($email = $congDan->getEmail()) && ($donViTiepNhan = $this->getDonViTiepNhan())) {
    //         $body = new Mime\Message();
    //         $noiDungSMS = '<html><head></head><body>';
    //         $noiDungSMS .= sprintf('<p style=margin-bottom:25px><b>Xin chào ông/bà %s,</b></p>', $congDan->getTenCongDan());
    //         $noiDungSMS .= sprintf('<p style=margin-bottom:25px>Số hồ sơ: %s</p>', $this->getSoHoSo());
    //         $noiDungSMS .= sprintf('<p style=margin-bottom:25px>Người đại diện: %s</p>', $congDan->getTenCongDan());
    //         $noiDungSMS .= sprintf('<p style=margin-bottom:25px>Tên lĩnh vực: %s</p>', $this->getThuTuc()->getLinhVuc()->getTenLinhVuc());
    //         $noiDungSMS .= sprintf('<p style=margin-bottom:25px>Tên thủ tục: %s</p>', $this->getThuTuc()->getTenThuTuc());
    //         $noiDungSMS .= sprintf('<p style=margin-bottom:25px>Tên cơ quan: %s</p>', $this->getCoQuan()->getTenCoQuan());
    //         $noiDungSMS .= sprintf('<p style=margin-bottom:25px>Hình thức nhận kết quả: %s</p>', $this->getHinhThucNhanKetQua()->getTenHinhThuc());
    //         // $noiDungSMS .= sprintf('<p style=margin-bottom:25px>Ngày tiếp nhận: %s</p>', $this->getNgayTiepNhan('d/m/Y H:i:s'));
    //         // $noiDungSMS .= sprintf('<p style=margin-bottom:25px>Ngày hẹn trả: %s</p>', $this->getNgayHenTra('d/m/Y H:i:s'));
    //         $noiDungSMS .= sprintf('<p style=margin-bottom:25px>Trạng Thái hồ sơ: %s</p>', $trangThai);
    //         $noiDungSMS .= '<p>Để theo dõi được kết quả giải quyết hồ sơ online Ông/Bà vui lòng xem hướng dẫn tra cứu tại địa chỉ: </p>';
    //         $noiDungSMS .= '<a href = "https://www.youtube.com/watch?v=1O94PgfcM5U">https://www.youtube.com/watch?v=1O94PgfcM5U</a>';
    //         $noiDungSMS .= sprintf('<p style=margin-bottom:25px>Địa chỉ tra cứu: <a href = "%s">%s</a></p>', $url, $url);
    //         // hiển thị nội dung gửi mail đã đc cấu hình trong thủ tục
    //         $noiDungSMS .= sprintf('<p>%s</p>',$ndMailCH);

    //         $noiDungSMS .= sprintf('<p style=margin-top:25px>Trân trọng!</p>');
    //         $noiDungSMS .= '</body></html>';
    //         $part = new Mime\Part($noiDungSMS);
    //         $part->setType(Mime\Mime::TYPE_HTML);
    //         $body->addPart($part);
    //         $message = new Mail\Message();
    //         $message->addFrom(Sys\Parameter::fromId('mail_username')->getValue());
    //         $message->addTo($email);
    //         $message->setSubject(sprintf('VNPT iGate - Thông báo đã nộp hồ sơ %s', $this->getSoHoSo()));
    //         $message->setBody($body);
    //         return System::getDefaultSmtpTranport()->send($message);
    //     } elseif ($this->getEmailCongDan() != '' && !empty($this->getEmailCongDan())) {
    //         $body = new Mime\Message();
    //         $noiDungSMS = '<html><head></head><body>';
    //         $noiDungSMS .= sprintf('<p style=margin-bottom:25px><b>Xin chào ông/bà %s,</b></p>', $this->getTenCongDanNop());
    //         $noiDungSMS .= sprintf('<p style=margin-bottom:25px>Số hồ sơ: %s</p>', $this->getSoHoSo());
    //         $noiDungSMS .= sprintf('<p style=margin-bottom:25px>Người đại diện: %s</p>', $this->getTenCongDanNop());
    //         $noiDungSMS .= sprintf('<p style=margin-bottom:25px>Tên lĩnh vực: %s</p>', $this->getThuTuc()->getLinhVuc()->getTenLinhVuc());
    //         $noiDungSMS .= sprintf('<p style=margin-bottom:25px>Tên thủ tục: %s</p>', $this->getThuTuc()->getTenThuTuc());
    //         $noiDungSMS .= sprintf('<p style=margin-bottom:25px>Tên cơ quan: %s</p>', $this->getCoQuan()->getTenCoQuan());
    //         $noiDungSMS .= sprintf('<p style=margin-bottom:25px>Hình thức nhận kết quả: %s</p>', $this->getHinhThucNhanKetQua()->getTenHinhThuc());
    //         // $noiDungSMS .= sprintf('<p style=margin-bottom:25px>Ngày tiếp nhận: %s</p>', $this->getNgayTiepNhan('d/m/Y H:i:s'));
    //         // $noiDungSMS .= sprintf('<p style=margin-bottom:25px>Ngày hẹn trả: %s</p>', $this->getNgayHenTra('d/m/Y H:i:s'));
    //         $noiDungSMS .= sprintf('<p style=margin-bottom:25px>Trạng Thái hồ sơ: %s</p>', $trangThai);
    //         $noiDungSMS .= '<p>Để theo dõi được kết quả giải quyết hồ sơ online Ông/Bà vui lòng xem hướng dẫn tra cứu tại địa chỉ: </p>';
    //         $noiDungSMS .= '<a href = "https://www.youtube.com/watch?v=1O94PgfcM5U">https://www.youtube.com/watch?v=1O94PgfcM5U</a>';
    //         $noiDungSMS .= sprintf('<p style=margin-bottom:25px>Địa chỉ tra cứu: <a href = "%s">%s</a></p>', $url, $url);
    //         $noiDungSMS .= sprintf('<p style=margin-top:25px>Trân trọng!</p>');
    //         $noiDungSMS .= '</body></html>';
    //         $part = new Mime\Part($noiDungSMS);
    //         $part->setType(Mime\Mime::TYPE_HTML);
    //         $body->addPart($part);
    //         $message = new Mail\Message();
    //         $message->addFrom(Sys\Parameter::fromId('mail_username')->getValue());
    //         $message->addTo($email);
    //         $message->setSubject(sprintf('VNPT iGate - Thông báo đã nộp hồ sơ %s', $this->getSoHoSo()));
    //         $message->setBody($body);
    //         return System::getDefaultSmtpTranport()->send($message);
    //     }
    // }

    public function guiSMSDaNop() {
        $this->guiSMSDaNopDenNguoiNop();
        if ((int) Entity\System\Parameter::fromId('DVC_HSOL_SMS_TO_CB')->getValue() === 1) {
            $this->guiSMSDaNopDenCanBoMotCua();
        }
    }

    public function guiSMSDaNopDenNguoiNop() {
        $soHoSo = $this->getSoHoSo();
        $congDan = $this->getCongDan();
        $diDong = $congDan->getDiDong();
        // Nhin 8661 Cấu hình gửi thông báo cho công dân theo quy trình thủ tục
        $maQTTT = $this->getMaQttt();
        $cauHinhSMSNopOnline = (new StoreProcedure\SELECT_CONTENT_SMS_QTTT([
            'P_MA_QTTT' => $maQTTT,
            'P_TYPE' => 1 //Nộp hồ sơ online
        ]))->getDefaultResult(0);
        if (($this->getTrangThaiHoSo() === 'DA_NOP' && $soHoSo && $diDong) or ($this->getTrangThaiHoSo() === 'DA_BO_SUNG_VA_NOP_LAI' && $soHoSo && $diDong)) {
            $guiSMSCoDau = Entity\System\Parameter::fromId('SMS_GUI_TIN_NHAN_CO_DAU')->getValue();
            $sms = new SMS\SMS($diDong);
            if ($tenDangNhap = $congDan->getTenDangNhap()) {
                if(!empty($cauHinhSMSNopOnline['CONTENT']) && $cauHinhSMSNopOnline['ID'] != 0){
                    $noiDungSMS = $this->replaceVariableHS($cauHinhSMSNopOnline['CONTENT']);
                    $sms->setContent(sprintf("%s",$noiDungSMS));
                }else{
                    if($guiSMSCoDau == 1)
                        $sms->setContent(sprintf("Đã nộp hồ sơ %s. Tài khoản nộp: %s", $soHoSo, $tenDangNhap));
                    else
                        $sms->setContent(sprintf("Da nop ho so %s. Tai khoan nop: %s", $soHoSo, $tenDangNhap));
                }
            } else {
                if(!empty($cauHinhSMSNopOnline['CONTENT']) && $cauHinhSMSNopOnline['ID'] != 0){
                    $noiDungSMS = $this->replaceVariableHS($cauHinhSMSNopOnline['CONTENT']);
                    $sms->setContent(sprintf("%s",$noiDungSMS));
                }else{
                    if($guiSMSCoDau == 1)
                        $sms->setContent(sprintf('Đã nộp hồ sơ %s. Khoá cập nhật: %s', $soHoSo, $this->getKhoaCapNhat()));
                    else
                        $sms->setContent(sprintf('Da nop ho so %s. Khoa cap nhat: %s', $soHoSo, $this->getKhoaCapNhat()));
                }

            }
            $logSMS = new \Oracle\OracleFunction\INSERT_LOG_THONG_BAO(array(
                'P_MA_HO_SO_ONLINE' => $this->getMaHoSo() ?: '',
                'P_MA_CAN_BO_THUC_HIEN' => '',
                'P_MA_THAO_TAC' => 4,
                'P_NOI_DUNG' => $sms->getContent() ?: ''
            ));

            (new SMS\Brandname($sms, $this->getCoQuan(), $this->getCoQuan()->getMaCoQuan()))->send();
        }
    }

    public function guiSMSDaNopDenCanBoMotCua() {
        if ($soHoSo = $this->getSoHoSo()) {
            $guiSMSCoDau = Entity\System\Parameter::fromId('SMS_GUI_TIN_NHAN_CO_DAU')->getValue();
            //goi sms toi tung can bo cua to chuyen mon dc cau hinh
            if ((int) Entity\System\Parameter::fromId('SEND_SMS_TO_CB_TCM')->getValue() === 1) {
                $dmCanBoMotCua = (new Model\SmsCanBoTCM())->goiSMS($this->getMaCoQuan(),$this->getMaQttt(), $this->getMaDonViTiepNhan());
                if($guiSMSCoDau == 1)
                    $brandname = new SMS\Brandname(new SMS\SMS(null, sprintf('Có hồ sơ mới nộp: %s', $soHoSo)));
                else
                    $brandname = new SMS\Brandname(new SMS\SMS(null, sprintf('Co ho so moi nop: %s', $soHoSo)));
                foreach ($dmCanBoMotCua as $canBo) {
                    if ($diDong = $canBo->getDiDong()) {
                        $brandname->getSMS()->setMobile($diDong);
                        $brandname->send();
                    }
                }
            } else {
                $dmCanBoMotCua = new DanhMuc\CanBo([
                    'provider' => DanhMuc\CanBo::BO_PHAN_MOT_CUA,
                    'maDonVi' => $this->getMaDonViTiepNhan()
                ]);
                if($guiSMSCoDau == 1)
                    $brandname = new SMS\Brandname(new SMS\SMS(null, sprintf('Có hồ sơ mới nộp: %s', $soHoSo)));
                else
                    $brandname = new SMS\Brandname(new SMS\SMS(null, sprintf('Co ho so moi nop: %s', $soHoSo)));
                foreach ($dmCanBoMotCua->getItems() as $canBo) {
                    if ($diDong = $canBo->getDiDong()) {
                        $brandname->getSMS()->setMobile($diDong);
                        $brandname->send();
                    }
                }
            }

        }
    }

    public function duocPhepCapNhat() {
        if ($maHoSo = $this->getMaHoSo()) {
            return (new Package\DVC_HS())->DUOC_PHEP_CAP_NHAT(['P_MA_HO_SO' => $maHoSo]) === 1;
        }
        return true;
    }

    public function duocPhepHuy() {
        if ($maHoSo = $this->getMaHoSo()) {
            return (new Package\DVC_HS())->DUOC_PHEP_HUY(['P_MA_HO_SO' => $maHoSo]) === 1;
        }
    }

    public function duocPhepTiepNhan() {
        if (!$this->daTiepNhan()) {
            if ((int) $this->getQttt()->getDieuKienTiepNhan() === 1 && $this->daYeuCauThuGom()) {//Nhan vien buu dien da giao ho so den
                return $this->getHangGuiThuGom()->daGuiDen();
            }
            return in_array($this->getTrangThaiHoSo(), [
                 'DA_NOP'
                , 'CHO_BO_SUNG'
                , 'DA_BO_SUNG_VA_NOP_LAI'
                , 'THAM_XET_DAT_ONLINE'
            ]);
        }
    }

    public function duocPhepYeuCauBoSung() {
        if ($this->getMaHoSo() && !$this->daYeuCauThuGom()) {
            return in_array($this->getTrangThaiHoSo(), [
                'DA_NOP'
                , 'CHO_BO_SUNG'
                , 'DA_BO_SUNG_VA_NOP_LAI'
                , 'THAM_XET_DAT_ONLINE'
            ]);
        }
    }

    public function dangLuuNhungKhongNop() {
        return $this->getTrangThaiHoSo() === 'LUU_NHUNG_KHONG_NOP';
    }

    public function dangCapNhat() {
        return $this->getTrangThaiHoSo() === 'DANG_CAP_NHAT';
    }

    public function daTiepNhan() {
        return ($maHoSo = $this->getMaHoSo()) && (new Package\DVC_HS())->DA_TIEP_NHAN(['P_MA_HO_SO' => $maHoSo]) === 1;
    }

    public function daHuy() {
        return $this->getTrangThaiHoSo() === 'DA_HUY';
    }

    public function layTinhTrangHoSo() {
        $TT_THEO_TT_22_ACTIVE = Entity\System\Parameter::fromId('TT_THEO_TT_22_ACTIVE')->getValue();
        if($TT_THEO_TT_22_ACTIVE==1){
            $hoSo = Entity\HoSo::fromMaHoSoOnline($this->getMaHoSo());
            if($hoSo->exists()){
                return (new Package\MC_HS())->GET_TINH_TRANG_HS_TT22(['P_MA_HO_SO' => $hoSo->getMaHoSo(), 'P_MA_HO_SO_ONLINE' => $this->getMaHoSo()]);
            }else{
                return (new Package\MC_HS())->GET_TINH_TRANG_HS_TT22(['P_MA_HO_SO_ONLINE' => $this->getMaHoSo()]);
            }
        }else{
            return (new Package\MC_HS())->GET_TINH_TRANG_HS(['P_MA_HO_SO_ONLINE' => $this->getMaHoSo()]);
        }
    }

    public function updateSmartGatePaymentRequestId($smartGatePaymentRequestId) {
        if ($maHoSo = $this->getMaHoSo()) {
            $sp = new OracleFunction\UPDATE_HS_SMARTGATE_PMREQID([
                'P_MA_HO_SO_ONLINE' => $maHoSo,
                'P_SMARTGATE_PAYMENT_REQUEST_ID' => $smartGatePaymentRequestId
            ]);
            if ($sp->getResult() === 1) {
                $this->setSmartGatePaymentRequestId($smartGatePaymentRequestId);
                return true;
            }
            $this->setSmartGatePaymentRequestId(null);
            return false;
        }
    }

    public function updateVNPTPayInitRequestId($initRequestId) {
        if ($maHoSo = $this->getMaHoSo()) {
            $sp = new OracleFunction\UPDATE_HS_VNPTPAY_INIT_REQID([
                'P_MA_HO_SO_ONLINE' => $maHoSo,
                'P_VNPTPAY_INIT_REQUEST_ID' => $initRequestId
            ]);
            if ($sp->getResult() === 1) {
                $this->setSmartGatePaymentRequestId($initRequestId);
                return true;
            }
            $this->setSmartGatePaymentRequestId(null);
            return false;
        }
    }

    /**
     * @method huyHoSo xoa ho so hoac cap nhat trang thai thanh da huy
     * @param boolean $permanently true se xoa vinh vien ho so
     * @return ResultInfo 1 la thanh cong nguoc lai la that bai
     */
    public function huyHoSo($permanently = false,$simpleResult  = false) {
        $conn = Connection::getConnection();
        $conn->turnOffAutoCommit();
        if ($this->daYeuCauThuGom()) {
            if (!$this->huyYeuCauThuGom()) {
                $conn->rollback();
                $conn->turnOnAutoCommit();
                return new ResultInfo(0, 'Không thể hủy hồ sơ đã có yêu cầu thu gom');
            }
        }
        if ($permanently) {
            $params = ['P_MA_HO_SO' => $this->getMaHoSo()];
            if ((new Package\MC_HSOL())->DELETE_MC_HSOL($params)) {
                $params['P_ERROR'] = 'Hủy hồ sơ thành công';
                $this->xoaTatCaTepTinTrenDia();
                // lưu log tác động hồ sơ online
                $rs = (new OracleFunction\UPDATE_LOG_HO_SO_ONLINE([
                    'P_SO_HO_SO'  => $this->getSoHoSo(),
                    'P_MA_THAO_TAC' => 'CONG_DAN_XOA',
                    'P_NOI_DUNG_THAO_TAC' => $this->getMaCanBoHuy() ? 'Cán bộ hủy/xóa hồ sơ online': 'Công dân hủy/xóa hồ sơ online',
                    'P_TEN_CONG_DAN'    => $this->getCongDan() ? $this->getCongDan()->getTenCongDan() : '',
                    'P_MA_CAN_BO' => $this->getMaCanBoHuy()
                ]))->getResult();
                $conn->commit();
            } else {
                $conn->rollback();
            }
            $conn->turnOnAutoCommit();
            return new ResultInfo($params['P_KQ'], $params['P_ERROR']);
        } else {
            if ((new Package\MC_HSOL())->HUY_HO_SO_ONLINE([
                'P_MA_HO_SO' => $this->getMaHoSo(),
                'P_MA_CAN_BO' => $this->getMaCanBoHuy(),
                'P_LY_DO_HUY' => $this->getLyDoHuy(),
                'P_FILE_HUY' => $this->getFileHuy()
            ])) {
                $rs = (new OracleFunction\UPDATE_LOG_HO_SO_ONLINE([
                    'P_SO_HO_SO'  => $this->getSoHoSo(),
                    'P_MA_THAO_TAC' => 'DA_HUY',
                    'P_NOI_DUNG_THAO_TAC' => 'Cán bộ hủy hồ sơ online: ' . $this->getLyDoHuy(),
                    'P_TEN_CONG_DAN'    => $this->getCongDan() ? $this->getCongDan()->getTenCongDan() : '',
                    'P_MA_CAN_BO' => $this->getMaCanBoHuy()
                ]))->getResult();
                $conn->commit();
                $conn->turnOnAutoCommit();
                if($simpleResult){
                    return true;
                }
                return new ResultInfo(1, 'Hủy hồ sơ thành công');
            } else {
                $conn->rollback();
                $conn->turnOnAutoCommit();
                if($simpleResult){
                    return false;
                }
                return new ResultInfo(0, 'Hủy hồ sơ thất bại');
            }
        }
    }

    public function tamDung($editable = false) {
        if ($editable) {
            return $this->updateTrangThaiHoSo(self::ChoBoSung);
        }
        return $this->updateTrangThaiHoSo(self::DangTamDung);
    }

    public function yeuCauBoSung($noiDungBongSung = '', $maCanBo = '', $file = '',$ngayYCBS = '') {
        $params = ['P_MA_HO_SO' => $this->getMaHoSo(),
                'P_MA_CAN_BO' => $maCanBo,
                'P_NOI_DUNG_YCBS' => $noiDungBongSung,
                'P_FILE_YCBS' => $file,
                'P_NGAY_YCBS' => $ngayYCBS];
        if ((new Package\MC_HSOL())->YCBS_HO_SO_ONLINE($params)) {
            return true;
        }
        return false;
    }

    public function lre(array $options = array()) {
        if (count($options = $this->remapLreOptions($options))) {
            if (isset($options['CongDan'])) {
                $this->setCongDan(Entity\CongDan::fromMaHoSoOnline($this->getMaHoSo(), ['lre_options' => $options['CongDan']]));
                unset($options['CongDan']);
            }

            if (isset($options['CanBoThamXet'])) {
                $this->setCanBoTxhsOnline(Entity\CanBo::fromMaCanBo($this->maCanBoTxhsOnline, ['lre_options' => $options['CanBoThamXet']]));
                unset($options['CanBoThamXet']);
            }

            if (isset($options['DmLePhiHoSo'])) {
                $this->setDmLePhiHoSo(new DanhMuc\LePhiHoSo(['maHoSoOnline' => $this->getMaHoSo(), 'lre_options' => $options['DmLePhiHoSo']]));
                unset($options['DmLePhiHoSo']);
            }
            if (isset($options['DanhSachGiayToNop'])) {
                $this->setDanhSachGiayToNop(new DanhMuc\GiayToCuaHoSoOnline([
                    'provider' => DanhMuc\GiayToCuaHoSoOnline::HO_SO_ONLINE,
                    'lre_options' => $options['DanhSachGiayToNop'],
                    'maHoSoOnline' => $this->getMaHoSo()
                ]));
                unset($options['DanhSachGiayToNop']);
            }
            if (isset($options['DmGiayToKhac'])) {
                $this->setDmGiayToKhac(new DanhMuc\GiayToKhacCuaHoSo([
                    'maHoSoOnline' => $this->getMaHoSo()
                ]));
                unset($options['DmGiayToKhac']);
            }
            parent::lre($options);
        }
    }

    public static function fromRecord($data, array $options = []) {
        $record = new ArrayObject($data, ArrayObject::ARRAY_AS_PROPS);
        $options = array_merge(['lre_options' => []], $options);
        $inst = new static();
        $inst->setMaHoSo($record->MA_HO_SO);
        $inst->setVeViec($record->VE_VIEC);
        $inst->setMaHangGuiThuGom($record->MA_HANG_GUI_THU_GOM);
        $inst->setMaHinhThucThanhToan($record->MA_HINH_THUC_THANH_TOAN);
        $inst->setMaPhuongXaThuGom($record->MA_PHUONG_XA_THU_GOM);
        $inst->setMaDonViTiepNhan($record->MA_DON_VI_TIEP_NHAN);
        $inst->setMaCoQuan($record->MA_CO_QUAN);
        $inst->setMaBieuMau($record->MA_BIEU_MAU);
        $inst->setMaBieuMauNguoiNop($record->MA_BIEU_MAU_NGUOI_NOP);
        $inst->setDuLieuBieuMau(Characters::fromClob($record->DU_LIEU_BIEU_MAU));
        $inst->setGhiChu($record->GHI_CHU);
        $inst->setGiayToKhac($record->HO_SO_GOM_CO);
        $inst->setFileGiayToKhac($record->GIAY_TO_KHAC);
        $inst->setFileDaThanhToan($record->FILE_DA_THANH_TOAN);
        $inst->setMaHinhThucNop($record->MA_HINH_THUC_NOP);
        $inst->setDiaChiThuGom($record->DIA_CHI_THU_GOM);
        $inst->setNgayYeuCauThuGom(DateTime::createFromFormat('d/m/Y H:i:s', $record->NGAY_YEU_CAU_THU_GOM) ? : null);
        $inst->setMaHinhThucNhanKetQua($record->HINH_THUC_NHAN_KQ);
        $inst->setMaPhuongXaNhanKetQua($record->MA_PHUONG_XA_NHAN_KQ);
        $inst->setDiaChiNhanKetQua($record->DIA_CHI_NHAN_KQ);
        $inst->setMaHoSoTiepNhan($record->MA_HO_SO_TIEP_NHAN);
        $inst->setMaCongDan($record->MA_CONG_DAN);
        $inst->setMaQttt($record->MA_QTTT);
        $inst->setTrangThaiHoSo($record->TRANG_THAI_HO_SO);
        $inst->setKhoaCapNhat($record->KHOA_CAP_NHAT);
        $inst->setNoiDungYCBS($record->NOI_DUNG_YCBS);
        $inst->setNgayYCBS($record->NGAY_YCBS);
        $inst->setSoHoSo($record->SO_HO_SO);
        $inst->sethsNopTuCDVCQG($record->HS_NOP_TU_CDVCQG);
        $inst->setmaHoSoCDVCQG($record->MA_HO_SO_DVCQG);
        $inst->setMaQuanHuyenNop($record->MA_QUAN_HUYEN_TIEP_NHAN);
        $inst->setMaPhuongXaNop($record->MA_PHUONG_XA_TIEP_NHAN);
        $inst->setMaThuTuc($record->MA_THU_TUC);
        $inst->setVNPTPayInitRequestId($record->VNPTPAY_INIT_REQUEST_ID);
        $inst->setTenCongDanNop($record->TEN_CONG_DAN_NOP);
        $inst->setEmailCongDan($record->EMAIL_CONG_DAN);
        $inst->setDiaChiCongDanNop($record->DIA_CHI_CONG_DAN_NOP);
        $inst->setMaPhuongXaNguoiNop($record->MA_PHUONG_XA_NGUOI_NOP);
        $inst->setCmndCongDanNop($record->CMND_CONG_DAN_NOP);
        $inst->setDiDongLienLacCongDan($record->DI_DONG_LIEN_LAC_CONG_DAN);
        $inst->setTenCongDanNop($record->TEN_CONG_DAN_NOP);
        $inst->setSoGCNGP($record->SO_GIAY_CHUNG_NHAN);
        $inst->setNgayCapGCNGP($record->NGAY_CAP_GCNGP);
        $inst->setNoiCapGCNGP($record->NOI_CAP_GCNGP);
        $inst->setMaTinhCapCMND($record->MA_TINH_CAP_CMND);
        $inst->setMaCanBoTxhsOnline($record->MA_CB_TXHS_ONLINE);
        $inst->setFileYCBS($record->FILE_YCBS);
        $inst->setFileBienLaiPaymentPlatform($record->FILE_BIEN_LAI_PAYMENT_PLATFORM);
        $inst->setTrangThaiDongBoDVCQG($record->TRANG_THAI_LIEN_THONG_DVCQG);
        $inst->setFileChungTuDatDai($record->FILE_CHUNG_TU_DAT_DAI);
        $inst->setFileHuy($record->FILE_HUY);
        $inst->lre($options['lre_options']);
        return $inst;
    }

    public static function fromProperties($data, array $options = array()) {
        $options = array_merge(['lre_options' => []], $options);
        $props = new ArrayObject($data, ArrayObject::ARRAY_AS_PROPS);
        if (is_string($props->ngayHuy)) {
            $props->ngayHuy = DateTime::createFromFormat('d/m/Y H:i', $props->ngayHuy) ? : null;
        }
        $inst = parent::fromProperties($data, $options);
        $inst->setMaHoSoTiepNhan($props->maHoSoTiepNhan);
        $inst->setNgayHuy($props->ngayHuy);
        $inst->setFileHuy($props->fileHuy);
        return $inst;
    }

    public static function fromMaHoSo($maHoSo, array $options = []) {
        return self::fromRecord($maHoSo ? (new Package\ENTITY())->LAY_HO_SO_ONLINE(['P_MA_HO_SO_ONLINE' => $maHoSo]) : [], $options);
    }
    public static function fromSoHoSo($soHoSo, array $options = []) {
        return self::fromRecord($soHoSo ? (new Package\ENTITY())->LAY_HO_SO_ONLINE([
            'P_SO_HO_SO'=> $soHoSo]) : [], $options);
    }
    public static function fromMaHoSoDVCQG($maHoSo, array $options = []) {
        return self::fromRecord($maHoSo ? (new Package\ENTITY())->LAY_HO_SO_ONLINE(['P_MA_HO_SO_DVCQG' => $maHoSo]) : [], $options);
    }

    public function guiYeuCauThuGom_VNPOST() {
        if ($this->daYeuCauThuGom()) {
            if (!$this->huyYeuCauThuGom()) {
                return false;
            }
        }
        //Chia 2 trường hợp
        $maHoSo = $this->getMaHoSo();
        $package2 = new Package\VNPOST();
        $dataHS = ['P_MA_HO_SO_ONLINE' => $maHoSo];
        $rs22 = $package2->YEU_CAU_THU_GOM($dataHS);

        if ($rs22 === 1) {
            try {
                $maHangGuiThuGom = $dataHS['P_MA_HANG_GUI'];
                $this->INSERT_LOG('Mã hàng gửi thu gom VNPOST qua LGSP : ' . $maHangGuiThuGom, 0);

                $LGSP_VNPOST_GETPRICE_USER = Model\Entity\System\Parameter::fromId('LGSP_VNPOST_GETPRICE_USER')->getValue();

                // Hienctt KV1 - VNPOST LCI - Lấy mã CustomerCode theo mã CRM do LCI quy định
                $LGSP_VNPOST_LCI_CONNECT = Model\Entity\System\Parameter::fromId('LGSP_VNPOST_LCI_CONNECT_CRM')->getValue();
                if($LGSP_VNPOST_LCI_CONNECT == 1) {
                    $LGSP_VNPOST_CRM = $package2->SELECT_MA_CRM_THEO_MA_CQ(['P_MA_CO_QUAN' => $this->getMaCoQuan()]);
                    if($LGSP_VNPOST_CRM) {
                        $LGSP_VNPOST_GETPRICE_USER = $LGSP_VNPOST_CRM;
                    }
                }

                $datat = $this->callAPIGetKeyToken();
                $token_access = $datat['access_token'];

                $soTienThuHo = 0;

                $vnpostYeuCau = Entity\VNPost\YeuCau::fromMaHangGui($maHangGuiThuGom);
                $content = $vnpostYeuCau->createDataOrder();
                $items = $content->getItems();

                foreach ((array)$items as $key => $value) {
                    $item               = $value[0];
                    $sender             = $item->getSender();
                    $receiver           = $item->getReceiver();
                    
                    $soTienThuHo        = $item->getCharge();
                    $senderDescripttion = $item->getDesc();
                    
                    $provCode_sender    = $sender->getProvCode();
                    $distCode_sender    = $sender->getDistCode();
                    $commCode_sender    = $sender->getCommCode();
                    $diachicongdan      = $sender->getAddress();
                    $tenCongDan         = $sender->getName();
                    $diDongCongDan      = $sender->getPhone();
                    $emailCongDan       = $sender->getMail();
                    
                    $provCode_receiver  = $receiver->getProvCode();
                    $distCode_receiver  = $receiver->getDistCode();
                    $commCode_receiver  = $receiver->getCommCode();
                    $diaChiCoQuan       = $receiver->getAddress();
                    $tenCoQuan          = $receiver->getName();
                    $diDongCoQuan       = $receiver->getPhone();
                    $emailCoQuan        = $receiver->getMail();

                }
                if ($soTienThuHo > 0) {
                    $description = "Den nha nguoi dan thu gom ho so! Buu dien thu ho le phi ho so, so tien thu ho la: " . $soTienThuHo;
                } else {
                    $description = "Den nha nguoi dan thu gom ho so! Khong thu ho le phi ho so";
                }

                $LGSP_VNPOST_ADAPTER_URL_API = Model\Entity\System\Parameter::fromId('LGSP_VNPOST_ADAPTER_URL_API')->getValue();
                $LGSP_VNPOST_VTU_CONNECT = Model\Entity\System\Parameter::fromId('LGSP_VNPOST_VTU_CONNECT')->getValue();

                $congDan = $this->getCongDan();

                $txt_hinhThucNop = $this->getHinhThucNop()->getTenHinhThuc();

                //SenderDesc ( nội dung) : Số hồ sơ || Tên thủ tục || Thành phần hs : || cmnd || Dịch vụ: hình thức nộp - hình thức nhận kết quả
                $SenderDesc = 'Tên Thủ Tục: ' . $this->getThuTuc()->getTenThuTuc(). ' || Thành Phần: ' .  $senderDescripttion . ' || Cmnd: ' . $congDan->getSoCmnd();
                $SenderDesc = $SenderDesc . ' || Dịch vụ : ' . $txt_hinhThucNop;

                $body_content2 = array(
                    'CustomerCode'     => $LGSP_VNPOST_GETPRICE_USER,
                    'OrderNumber'      => $maHangGuiThuGom,
                    'CODAmount'        => $soTienThuHo,
                    'SenderProvince'   => $provCode_sender,
                    'SenderDistrict'   => $distCode_sender,
                    'SenderAddress'    => $diachicongdan,
                    'SenderName'       => $tenCongDan,
                    'SenderEmail'      => $emailCongDan,
                    'SenderTel'        => $diDongCongDan,
                    'SenderDesc'       => $SenderDesc,
                    'Description'      => $description,
                    'ReceiverName'     => $tenCoQuan,
                    'ReceiverAddress'  => $diaChiCoQuan,
                    'ReceiverTel'      => $diDongCoQuan,
                    'ReceiverProvince' => $provCode_receiver,
                    'ReceiverDistrict' => $distCode_receiver,
                    'ReceiverEmail'    => $emailCoQuan
                );

                $json_body2 = json_encode($body_content2);
                $client2 = new Client();
                $adapter2 = new Client\Adapter\Curl();
                $adapter2->setOptions(array(
                    'curloptions' => array(
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_SSL_VERIFYHOST => false
                    )
                ));
                $client2->setAdapter($adapter2);
                $client2->setOptions(array(
                    'maxredirects' => 0,
                    'timeout' => 30
                ));
                $client2->setMethod('POST');
                if ($LGSP_VNPOST_VTU_CONNECT == '1') {//VNPOST VTU
                    $LGSP_VNPOST_ADAPTER_URL_API = $LGSP_VNPOST_ADAPTER_URL_API . '?serviceType=VNPost&serviceName=OrderPost';
                    $client2->setHeaders(array(
                        'Authorization' => 'Bearer ' . $token_access,
                        'Content-Type' => 'application/json'
                    ));
                } else {//VNPOST LCI
                    $client2->setHeaders(array(
                        'Authorization' => 'Bearer ' . $token_access,
                        'Content-Type' => 'application/json',
                        'service-code' => 'vnpost_post_order'
                    ));
                }
                $client2->setRawBody($json_body2);
                $client2->setUri($LGSP_VNPOST_ADAPTER_URL_API);
                $response2 = $client2->send();

                if ($response2->isSuccess()) {
                    $this->setMaHangGuiThuGom($maHangGuiThuGom);
                    $vnpostYeuCau->updateTrangThaiTo(0);
                    $data2 = json_decode($response2->getBody(), true);
                    $this->INSERT_LOG('[VNPOST][HO_SO_ONLINE][' . $this->getSoHoSo() . ']GUI YEU CAU DON HANG HS VNPOST LGSP THANH CONG!'
                            . 'Thong tin API : ' . json_encode($body_content2)
                            . ', Ket qua tra ve : Status=>' . $data2["Status"]
                            . ' va Message=>' . $data2["Message"], 0);
                    return true;
                } else {
                    $this->INSERT_LOG('[VNPOST][HO_SO_ONLINE][' . $this->getSoHoSo() . ']GUI YEU CAU DON HANG HS VNPOST LGSP THAT BAI!'
                            . 'Thong tin API : ' . json_encode($body_content2)
                            . ', Ket qua tra ve : ' . json_encode($response2->getBody(), true), 0);
                    return false;
                }
            } catch (Exception $ex) {
                $this->INSERT_LOG('[VNPOST][HO_SO_ONLINE][' . $this->getSoHoSo() . ']GUI YEU CAU DON HANG HS VNPOST LGSP THAT BAI! Error: ' . $ex, 0);
                return false;
            }
            //End  Đến đây
        }else{
            $this->INSERT_LOG('[VNPOST][HO_SO_ONLINE][' . $this->getSoHoSo() . ']Không vô được lấy mã hàng gửi : ' . $maHoSo, 0);
            $this->INSERT_LOG('[VNPOST][HO_SO_ONLINE][' . $this->getSoHoSo() . ']GUI YEU CAU DON HANG HS VNPOST LGSP THAT BAI!', 0);
            return false;
        }

    }
    /**
     * API lấy token
     */
    public function callAPIGetKeyToken() {
        $LGSP_CONSUMER_KEY = Model\Entity\System\Parameter::fromId('LGSP_CONSUMER_KEY')->getValue();
        $LGSP_CONSUMER_SECRET = Model\Entity\System\Parameter::fromId('LGSP_CONSUMER_SECRET')->getValue();
        $LGSP_VNPOST_TOKEN_ACCESS_API = Model\Entity\System\Parameter::fromId('LGSP_VNPOST_TOKEN_ACCESS_API')->getValue();

        if (!empty($LGSP_VNPOST_TOKEN_ACCESS_API)) {

            try {
                $request = new Request();
                $request->setMethod(Request::METHOD_POST);
                $request->setUri($LGSP_VNPOST_TOKEN_ACCESS_API);
                $request->getHeaders()->addHeaders(array(
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Accept' => 'application/json',
                    'grant_type' => 'client_credentials',
                    'Authorization' => 'Basic ' . base64_encode($LGSP_CONSUMER_KEY . ':' . $LGSP_CONSUMER_SECRET)
                ));
                $request->setQuery(new Parameters(array('grant_type' => 'client_credentials')));

                $client = new Client();
                $adapter = new Client\Adapter\Curl();
                $adapter->setOptions(array(
                    'curloptions' => array(
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_SSL_VERIFYHOST => false
                    )
                ));
                $client->setAdapter($adapter);
                $client->setOptions(array(
                    'maxredirects' => 0,
                    'timeout' => 30
                ));
                $response = $client->send($request);
                if ($response->isSuccess()) {
                    $data = json_decode($response->getBody(), true);
                } else {
                    $data = false;
                }

                return $data;
            } catch (Client\Adapter\Exception\TimeoutException $e) {
                $this->INSERT_LOG('[VNPOST][HO_SO_ONLINE][' . $this->getSoHoSo() . '] GET TOKEN LGSP THAT BAI! Error: ' . $ex, 0);
                return false;
            }
        } else {
            try {
                $request = new Request();
                $request->setMethod(Request::METHOD_POST);
                $request->setUri(self::TOKEN_ACCESS);
                $request->getHeaders()->addHeaders(array(
                    'Content-Type' => self::CONTENT_HEADER_TOKEN,
                ));
                $request->setContent(self::CONTENT_GET_TOKEN);

                $client = new Client();
                $adapter = new Client\Adapter\Curl();
                $adapter->setOptions(array(
                    'curloptions' => array(
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_SSL_VERIFYHOST => false
                    )
                ));
                $client->setAdapter($adapter);
                $client->setOptions(array(
                    'maxredirects' => 0,
                    'timeout' => 30
                ));
                $response = $client->send($request);
                if ($response->isSuccess()) {
                    $data = json_decode($response->getBody(), true);
                } else {
                    $data = false;
                }

                return $data;
            } catch (Client\Adapter\Exception\TimeoutException $e) {
                return false;
            }
        }
    }

    public function getTokenAccess($check_cache) {
        $dir = new Folder(self::FOLDER_SAVE_CACHE);
        if (!$dir->exists()) {
            $dir->create(0700, true);
        }
        $cache = StorageFactory::factory(array(
                    'adapter' => array(
                        'name' => 'Filesystem',
                        'options' => array(
                            'ttl' => self::CACH_TIME * 3600,
                            'cache_dir' => $dir->getPath()
                        )
                    ),
                    'plugins' => array('exception_handler' => array('throw_exceptions' => false))
        ));

        if ($check_cache) { // $check_cache = true: Nếu lấy dữ liệu từ cache
            if ($cache->hasItem(self::CACHE_KEY)) {
                $token_access = unserialize($cache->getItem(self::CACHE_KEY));
            } else {
                $token_access = self::callAPIGetKeyToken();
                $cache->setItem(self::CACHE_KEY, serialize($token_access));
            }
        } else { // $check_cache = false: Nếu không lấy dữ liệu từ cache thì gọi trực tiếp vào API lấy token
            $token_access = self::callAPIGetKeyToken();
            $cache->setItem(self::CACHE_KEY, serialize($token_access));
        }

        if (!empty($token_access['access_token'])) {
            return $token_access['access_token'];
        }

        return false;
    }

    public function INSERT_LOG($P_NOI_DUNG_THAO_TAC, $P_CO_THE_XOA = 1, $P_BAT_BUOC_INSERT = 0, $entity = null) {
        $P_MA_CAN_BO = 0;
        $P_CLIENT_IP = '113.164.236.59';
        $P_BROWSER = 'Google Chrome - 79.0.3945.134';
        $P_DEVICE = '';
        $CO_THE_INSERT = 1;
        $P_TITLE = '';
        $P_NGAY_THAO_TAC = date('d/m/Y H:i:s');
        $pck = new Package\MC_LOG();
        $pck->setConnection(Connection::create());
        return $pck->INSERT_LOG_CHUC_NANG([
            'P_MA_CAN_BO' => $P_MA_CAN_BO,
            'P_NOI_DUNG_THAO_TAC' => $P_NOI_DUNG_THAO_TAC,
            'P_CO_THE_XOA' => $P_CO_THE_XOA,
            'P_CLIENT_IP' => $P_CLIENT_IP,
            'P_BROWSER' => $P_BROWSER,
            'P_DEVICE' => $P_DEVICE,
            'P_ENTITY_NAME' => null,
            'P_ENTITY_ID' => null,
            'P_ENTITY_DATA' => null,
            'P_TITLE' => $P_TITLE,
            'P_NGAY_THAO_TAC' => date('d/m/Y H:i:s')
        ]);
    }
    
    //VNPOST BTE
    public function guiYeuCauThuGom_VNPOSTBTE() { 
        if ($this->daYeuCauThuGom()) {
            if (!$this->huyYeuCauThuGom()) {
                return false;
            }
        }
        //Chia 2 trường hợp
        $maHoSo = $this->getMaHoSo();
        $package2 = new Package\VNPOST();
        $dataHS = ['P_MA_HO_SO_ONLINE' => $maHoSo];
        $rs22 = $package2->YEU_CAU_THU_GOM($dataHS); 
        if ($rs22 === 1) {

            try {
                $maHangGuiThuGom = $dataHS['P_MA_HANG_GUI'];
                $this->INSERT_LOG('Mã hàng gửi thu gom VNPOST qua NGSP : ' . $maHangGuiThuGom, 0);

                $LGSP_VNPOST_GETPRICE_USER = Model\Entity\System\Parameter::fromId('LGSP_VNPOST_GETPRICE_USER')->getValue();
                $LGSP_NGSP_API_CONNECT_VNPOST = Model\Entity\System\Parameter::fromId('LGSP_NGSP_API_CONNECT_VNPOST')->getValueArray();
                $USE_BEARER_TOKEN_LGSP_SAVIS = Model\Entity\System\Parameter::fromId('USE_BEARER_TOKEN_LGSP_SAVIS')->getValue();
                // Hienctt KV1 - VNPOST LCI - Lấy mã CustomerCode theo mã CRM do LCI quy định
                $LGSP_VNPOST_LCI_CONNECT = Model\Entity\System\Parameter::fromId('LGSP_VNPOST_LCI_CONNECT_CRM')->getValue();
                if($LGSP_VNPOST_LCI_CONNECT == 1) {
                    $LGSP_VNPOST_CRM = $package2->SELECT_MA_CRM_THEO_MA_CQ(['P_MA_CO_QUAN' => $this->getMaCoQuan()]);
                    if($LGSP_VNPOST_CRM) {
                        $LGSP_VNPOST_GETPRICE_USER = $LGSP_VNPOST_CRM;
                    }
                }
                if (!empty($USE_BEARER_TOKEN_LGSP_SAVIS)) {
                     $token_access = $USE_BEARER_TOKEN_LGSP_SAVIS;
                }else{
                    $datat = $this->callAPIGetKeyTokenBTE();
                    $token_access = $datat['access_token'];
                }
                
                $soTienThuHo = 0;

                $vnpostYeuCau = Entity\VNPost\YeuCau::fromMaHangGui($maHangGuiThuGom);
                $content = $vnpostYeuCau->createDataOrder();
                $items = $content->getItems();

                foreach ((array)$items as $key => $value) {
                    $item               = $value[0];
                    $sender             = $item->getSender();
                    $receiver           = $item->getReceiver();
                    
                    $soTienThuHo        = $item->getCharge();
                    $senderDescripttion = $item->getDesc();
                    
                    $provCode_sender    = $sender->getProvCode();
                    $distCode_sender    = $sender->getDistCode();
                    $commCode_sender    = $sender->getCommCode();
                    $diachicongdan      = $sender->getAddress();
                    $tenCongDan         = $sender->getName();
                    $diDongCongDan      = $sender->getPhone();
                    $emailCongDan       = $sender->getMail();
                    
                    $provCode_receiver  = $receiver->getProvCode();
                    $distCode_receiver  = $receiver->getDistCode();
                    $commCode_receiver  = $receiver->getCommCode();
                    $diaChiCoQuan       = $receiver->getAddress();
                    $tenCoQuan          = $receiver->getName();
                    $diDongCoQuan       = $receiver->getPhone();
                    $emailCoQuan        = $receiver->getMail();

                }
                if ($soTienThuHo > 0) {
                    $description = "Den nha nguoi dan thu gom ho so! Buu dien thu ho le phi ho so, so tien thu ho la: " . $soTienThuHo;
                } else {
                    $description = "Den nha nguoi dan thu gom ho so! Khong thu ho le phi ho so";
                }

                $congDan = $this->getCongDan();
                $txt_hinhThucNop = $this->getHinhThucNop()->getTenHinhThuc();

                //SenderDesc ( nội dung) : Số hồ sơ || Tên thủ tục || Thành phần hs : || cmnd || Dịch vụ: hình thức nộp - hình thức nhận kết quả
                $SenderDesc = 'Tên Thủ Tục: ' . $this->getThuTuc()->getTenThuTuc(). ' || Thành Phần: ' .  $senderDescripttion . ' || Cmnd: ' . $congDan->getSoCmnd();
                $SenderDesc = $SenderDesc . ' || Dịch vụ : ' . $txt_hinhThucNop;

                $body_content2 = array(
                    'CustomerCode'     => $LGSP_VNPOST_GETPRICE_USER,
                    'OrderNumber'      => $maHangGuiThuGom,
                    'CODAmount'        => $soTienThuHo,
                    'SenderProvince'   => $provCode_sender,
                    'SenderDistrict'   => $distCode_sender,
                    'SenderAddress'    => $diachicongdan,
                    'SenderName'       => $tenCongDan,
                    'SenderEmail'      => $emailCongDan,
                    'SenderTel'        => $diDongCongDan,
                    'SenderDesc'       => $SenderDesc,
                    'Description'      => $description,
                    'ReceiverName'     => $tenCoQuan,
                    'ReceiverAddress'  => $diaChiCoQuan,
                    'ReceiverTel'      => $diDongCoQuan,
                    'ReceiverProvince' => $provCode_receiver,
                    'ReceiverDistrict' => $distCode_receiver,
                    'ReceiverEmail'    => $emailCoQuan
                );
                
                if (!empty($USE_BEARER_TOKEN_LGSP_SAVIS)) {
                       
                         $curl = curl_init();
                        curl_setopt($curl, CURLOPT_HTTPHEADER, [ "Authorization:".'Bearer ' . $token_access, 'Content-Type: application/json' ]);
                        curl_setopt($curl, CURLOPT_POST, 1);
                        curl_setopt($curl, CURLOPT_URL, $LGSP_NGSP_API_CONNECT_VNPOST['API_POST_ORDER']);
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($body_content2));
                        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
                        $LGSP_SAVIS_SSL_CERT = Model\Entity\System\Parameter::fromId('LGSP_SAVIS_SSL_CERT')->getValue();
                            if ($LGSP_SAVIS_SSL_CERT==1) {
                                $ssl_crt = realpath("apps/bentre/lgsp_cert/file.crt.pem");
                                $ssl_key= realpath("apps/bentre/lgsp_cert/file.key.pem");
                                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, $ssl_crt);
                                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $ssl_crt);
                                curl_setopt($curl, CURLOPT_SSLCERT, $ssl_crt);
                                curl_setopt($curl, CURLOPT_SSLKEY, $ssl_key);
                            }
                            else{
                                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
                                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
                            }
                        $result = curl_exec($curl);
                        curl_close($curl); 
                        $response = json_decode($result,true); 
                        if(isset($response['Status'])){
                            if ($response['Status']==100) {
                                $this->setMaHangGuiThuGom($maHangGuiThuGom);

                                $vnpostYeuCau->updateTrangThaiTo(0);
                                $this->INSERT_LOG('[VNPOST][HO_SO_ONLINE][' . $this->getSoHoSo() . ']GUI YEU CAU DON HANG HS VNPOST THONG QUA SAVIS THANH CONG!'
                                    . 'Thong tin API : ' . json_encode($body_content2)
                                    . ', Ket qua tra ve : Status=>' . $response['Status']
                                    . ' va Message=>' . $response['Message'], 0);
                                return true;
                            }else {//Thất bại
                                $this->INSERT_LOG('[VNPOST][HO_SO_ONLINE][' . $this->getSoHoSo() . ']GUI YEU CAU DON HANG HS VNPOST THONG QUA SAVIS THAT BAI!'
                                    . 'Thong tin API : ' . json_encode($body_content2)
                                    . ', Ket qua tra ve : Status=>' . $response['Status']
                                    . ' va Message=>' . $response['Message'], 0);
                                return false;
                            }

                        }else{
                             $this->INSERT_LOG('[VNPOST][HO_SO_ONLINE][' . $this->getSoHoSo() . ']GUI YEU CAU DON HANG HS VNPOST THONG QUA SAVIS THAT BAI!'
                                    . 'Thong tin API : ' . json_encode($body_content2)
                                    . ' Ket qua tra ve=>' . $result, 0);
                            return false;
                        }

                    }

                    $options4 = array(
                        'http' => array(
                            'header' => "Content-type: application/json\r\n" .
                                        "Authorization: Bearer " . $token_access . "\r\n",
                            'method' => 'POST',
                            'content' => json_encode($body_content2)
                        ),
                        "ssl"=>array(
                             "verify_peer"=>false,
                             "verify_peer_name"=>false
                            )
                    );

                    $context4 = stream_context_create($options4);
                    $result4 = file_get_contents($LGSP_NGSP_API_CONNECT_VNPOST['API_POST_ORDER'], false, $context4);
                
                if ($result4) {
                    $response = json_decode($result4,true); 
                    $Status = $response['Status'];
                    $Message = $response['Message'];
                    if($Status == '100'){//Thêm mới thành công
                        $this->setMaHangGuiThuGom($maHangGuiThuGom);

                        $vnpostYeuCau->updateTrangThaiTo(0);
                        $this->INSERT_LOG('[VNPOST][HO_SO_ONLINE][' . $this->getSoHoSo() . ']GUI YEU CAU DON HANG HS VNPOST BTE THANH CONG!'
                            . 'Thong tin API : ' . json_encode($body_content2)
                            . ', Ket qua tra ve : Status=>' . $Status
                            . ' va Message=>' . $Message, 0);
                        return true;
                    } else {//Thất bại
                        $this->INSERT_LOG('[VNPOST][HO_SO_ONLINE][' . $this->getSoHoSo() . ']GUI YEU CAU DON HANG HS VNPOST BTE THAT BAI!'
                            . 'Thong tin API : ' . json_encode($body_content2)
                            . ', Ket qua tra ve : Status=>' . $Status
                            . ' va Message=>' . $Message, 0);
                        return false;
                    }
                    
                } else {
                    $this->INSERT_LOG('[VNPOST][HO_SO_ONLINE][' . $this->getSoHoSo() . ']GUI YEU CAU DON HANG HS VNPOST BTE THAT BAI!'
                            . 'Thong tin API : ' . json_encode($body_content2)
                            . ' Ket qua tra ve=>' . $result4, 0);
                    return false;
                }

            } catch (Exception $e) {
                $this->INSERT_LOG('[VNPOST][HO_SO_ONLINE][' . $this->getSoHoSo() . ']GUI YEU CAU DON HANG HS VNPOST NGSP THAT BAI! Error: ' . $ex, 0);
                return false;
            }
            //End  Đến đây
        }else{
            $this->INSERT_LOG('Không vô được lấy mã hàng gửi : ' . $maHoSo, 0);
            $this->INSERT_LOG('GUI YEU CAU DON HANG HS VNPOST LCI THAT BAI!', 0);
            return false;
        }


    }
    
    //API token BTE
    public function getTokenAccessBTE($check_cache){
        $dir = new Folder(self::FOLDER_SAVE_CACHE);
        if (!$dir->exists()) {
            $dir->create(0700, true);
        }
        $cache = StorageFactory::factory(array(
            'adapter'=>array(
                'name'=>'Filesystem',
                'options'=>array(
                    'ttl'=> self::CACH_TIME * 3600,
                    'cache_dir'=>$dir->getPath()
                )
            ),
            'plugins'=>array( 'exception_handler' => array('throw_exceptions' => false))
        ));

        if($check_cache) { // $check_cache = true: Nếu lấy dữ liệu từ cache
            if ($cache->hasItem(self::CACHE_KEY)) { 
                $token_access = unserialize($cache->getItem(self::CACHE_KEY));
            } else {
                $token_access = self::callAPIGetKeyTokenBTE();
                $cache->setItem(self::CACHE_KEY, serialize($token_access));
            }
        } else { // $check_cache = false: Nếu không lấy dữ liệu từ cache thì gọi trực tiếp vào API lấy token

            $token_access = self::callAPIGetKeyTokenBTE();
            $cache->setItem(self::CACHE_KEY, serialize($token_access));
        }

        if(!empty($token_access['access_token'])) {
            return $token_access['access_token'];
        }

        return false;
    }

    public function callAPIGetKeyTokenBTE() 
    {
        try {
            $LGSP_NGSP_API_CONNECT['API_GET_TOKEN']='';

            $LGSP_NGSP_API_CONNECT = Entity\System\Parameter::fromId('LGSP_NGSP_API_CONNECT_VNPOST')->getValueArray();
            if ($LGSP_NGSP_API_CONNECT['API_GET_TOKEN']!='') {
                $LGSP_CONSUMER_KEY = $LGSP_NGSP_API_CONNECT['CONSUMER_KEY'];
                $LGSP_CONSUMER_SECRET = $LGSP_NGSP_API_CONNECT['CONSUMER_SECRET'];
                $LGSP_TOKEN_ACCESS_API = $LGSP_NGSP_API_CONNECT['API_GET_TOKEN'];
                $json_body = 'grant_type=client_credentials';
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_HTTPHEADER, [ "Authorization:".'Basic ' . base64_encode($LGSP_CONSUMER_KEY.':'.$LGSP_CONSUMER_SECRET), 'Content-Type: application/x-www-form-urlencoded' ]);
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_URL, $LGSP_TOKEN_ACCESS_API);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_POSTFIELDS,$json_body);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
                $result = curl_exec($curl);
                curl_close($curl);
                $data = json_decode($result,true);
                return $data;
            }else{
            
                $LGSP_NGSP_API_CONNECT = Entity\System\Parameter::fromId('LGSP_NGSP_API_CONNECT')->getValueArray();

                $LGSP_CONSUMER_KEY = $LGSP_NGSP_API_CONNECT['CONSUMER_KEY'];
                $LGSP_CONSUMER_SECRET = $LGSP_NGSP_API_CONNECT['CONSUMER_SECRET'];
                $LGSP_TOKEN_ACCESS_API = $LGSP_NGSP_API_CONNECT['API_GET_TOKEN'] . '?grant_type=client_credentials';
                $options = array(
                    'http' => array(
                        'header' => "Content-type: application/x-www-form-urlencoded\r\n"
                                    . "Authorization: Basic " . base64_encode($LGSP_CONSUMER_KEY.':'.$LGSP_CONSUMER_SECRET) . "\r\n",
                        'method' => 'POST'
                    ),
                    "ssl"=>array(
                     "verify_peer"=>false,
                     "verify_peer_name"=>false
                    )
                );
                $context = stream_context_create($options);
                $result = file_get_contents($LGSP_TOKEN_ACCESS_API, false, $context);
                $data = json_decode($result, true);
                return $data;
            }
        } catch (Exception $e) {
            $this->INSERT_LOG('[VNPOST][HO_SO_ONLINE][' . $this->getSoHoSo() . '] GET TOKEN NGSP THAT BAI! Error: ' . $ex, 0);
            return false;
        }
    }

    public function tuChoiHoSo($permanently = false, $simpleResult  = false)
    {
        $conn = Connection::getConnection();
        $conn->turnOffAutoCommit();
        if ($this->daYeuCauThuGom()) {
            if (!$this->huyYeuCauThuGom()) {
                $conn->rollback();
                $conn->turnOnAutoCommit();
                return new ResultInfo(0, 'Không thể từ chối hồ sơ đã có yêu cầu thu gom');
            }
        }

        if ((new Package\MC_HSOL())->TU_CHOI_HO_SO_ONLINE([
            'P_MA_HO_SO' => $this->getMaHoSo(),
            'P_MA_CAN_BO' => $this->getMaCanBoHuy(),
            'P_LY_DO_HUY' => $this->getLyDoHuy(),
            'P_FILE_HUY' => $this->getFileHuy()
        ])) {
            $rs = (new OracleFunction\UPDATE_LOG_HO_SO_ONLINE([
                'P_SO_HO_SO'  => $this->getSoHoSo(),
                'P_MA_THAO_TAC' => 'KHONG_DUOC_TIEP_NHAN',
                'P_NOI_DUNG_THAO_TAC' => 'Cán bộ từ chối hồ sơ online: ' . $this->getLyDoHuy(),
                'P_TEN_CONG_DAN'    => $this->getCongDan() ? $this->getCongDan()->getTenCongDan() : '',
                'P_MA_CAN_BO' => $this->getMaCanBoHuy()
            ]))->getResult();
            $conn->commit();
            $conn->turnOnAutoCommit();
            if ($simpleResult) {
                return true;
}
            return new ResultInfo(1, 'Hủy hồ sơ thành công');
        } else {
            $conn->rollback();
            $conn->turnOnAutoCommit();
            if ($simpleResult) {
                return false;
            }
            return new ResultInfo(0, 'Hủy hồ sơ thất bại');
        }
    }
    
    public function getDmGiayToKhacHoSo() {
        return new DanhMuc\GiayToKhacCuaHoSo([
            'maHoSoOnline' => $this->getMaHoSo()
        ]);
    }

    public function updateHinhThucThanhToan() {
        return (new OracleFunction\UPDATE_HT_THANH_TOAN_ONLINE([
            'P_MA_HO_SO'                => $this->getMaHoSo(),
            'P_MA_HINH_THUC_THANH_TOAN' => $this->getMaHinhThucThanhToan()
        ]))->getResult();
    }

    public function layThongTinFormThongTinNgNop(){
        $lbm =  $this->layMaEformID();
        $bm = [];
        if($lbm)
        {
            $bmTDL =  (new \Oracle\Package\BCA_DICH_VU_CONG())->SELECT_GIA_TRI_TDL_N([
                'P_MA_TRUONG_DU_LIEU' => null,
                'P_SID' => $this->maHoSo,
                'P_EFORM_ID' => $lbm,
                'P_MA_HO_SO' => null
            ]);
            foreach ($bmTDL as $key => $value) {
                $bm[$key] = $value;
            }
        }

        return $bm;
    }

}
