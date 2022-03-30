<?php

if (!defined('SERVER_ROOT')) {
    exit('No direct script access allowed');
}
require_once (SERVER_ROOT . 'apps' . DS . 'api' . DS . 'modules' . DS . 'helper' . DS . 'helper.php');

use Model\iOffice\Ws\TraGiayTo;
use Zend\Http\Client;
use Oracle\Package;

class boconganthutuc_Controller extends Controller {

    private $helper;

    public function __construct() {
        parent::__construct('api', 'bocongan');
        $this->helper = new api\helper();
        $this->model->makeLog(true);
    }

    /**
     * API lấy thông tin công dân từ kho dữ liệu dân cư
     * tham khao api/bocongan/callThongTinCDTuCongDC
     * Method: POST
     */
    public function callThongTinCDTuCongDC() {
        $this->helper->setHttpHeaders('application/json', 200);
        try
        {
            $thong_tin_cd = Session::get(TIEP_DAU_NGU_SESSION . 'DU_LIEU_CONG_DAN');

            if(!$thong_tin_cd || !$thong_tin_cd->P_CMND_CONG_DAN) {
                echo json_encode([
                    'status' => 1,
                    'msg' => 'Lỗi chưa đăng nhập hoặc không có số CMND / CCCD.'], JSON_UNESCAPED_SLASHES);
                exit;
            }

//            $text_url = $v_type == 'cccd' ? 'socancuoc-congdan' : 'sochungminhthu-congdan' ; COMMENT VÌ MẶC ĐỊNH LẤY DATA CCCD
            $API_DAN_CU = Model\Entity\System\Parameter::fromId('BCA_API_DAN_CU')->getValue();
            $CMND_CCCD_TEST = Model\Entity\System\Parameter::fromId('BCA_API_DAN_CU_TEST_CMND_CCCD')->getValue();
            $CMND_CCCD_TEST = $CMND_CCCD_TEST ? $CMND_CCCD_TEST : $thong_tin_cd->P_CMND_CONG_DAN ;
            $NGAY_SINH_TEST = Model\Entity\System\Parameter::fromId('BCA_API_DAN_CU_TEST_NGAY_SINH')->getValue();
            $NGAY_SINH_TEST = $NGAY_SINH_TEST ? $NGAY_SINH_TEST : $thong_tin_cd->P_NGAY_SINH ;

            $text_param = 'socancuoc-congdan'.'/' . $CMND_CCCD_TEST . '/' . $NGAY_SINH_TEST;
            if ($API_DAN_CU) {
                $API_DAN_CU = str_replace('PARAM_URL_GET_TTCD', $text_param, $API_DAN_CU);
            } else {
                $API_DAN_CU = 'http://10.159.29.18:9000/ords-kho-dan-cu/csdl_dancuqg/dancutracuu_vngoai';
                $API_DAN_CU = $API_DAN_CU . '/' . $text_param;
            }
            $API_DAN_CU = urldecode($API_DAN_CU);
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
                'timeout'      => 30
            ));
            $client->setMethod('GET');
            $client->setHeaders(array('Content-Type' => 'application/json'));
            $client->setUri($API_DAN_CU);
            $response = $client->send();
            if ($response->isSuccess()) {
                $data = $response->getBody();
                Session::set(TIEP_DAU_NGU_SESSION.'BCA_CU_TRU_TK_CONG_DAN_SSO_DVCQG', $data);

                echo json_encode([
                    'status' => 0,
                    'data' => $data,
                    'msg' => 'Thành công.'], JSON_UNESCAPED_SLASHES);
            } else {
                echo json_encode([
                    'status' => 2,
                    'msg' => 'Không tìm thấy thông tin công dân trong hệ thống CSDL Dân cư Quốc Gia'], JSON_UNESCAPED_SLASHES);
            }
            exit;
        } catch(Client\Adapter\Exception\TimeoutException $e) {
            echo json_encode([
                'status' => 1,
                'msg' => 'Lỗi.'], JSON_UNESCAPED_SLASHES);
            exit;
        }
    }

    // Dữ liệu session từ function callThongTinCDTuCongDC
    public function sessionCDSSODVCQG() {
        echo json_encode([
            'status' => 1,
            'data' => Session::get(TIEP_DAU_NGU_SESSION.'BCA_CU_TRU_TK_CONG_DAN_SSO_DVCQG')
        ], JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * API lấy thông tin công dân từ kho dữ liệu dân cư
     * tham khao api/boconganthutuc/kiemTraTKCDTuCSDLDC
     * Method: POST
     * http://10.159.29.27:7007/ords-kho-dan-cu/csdl_dancuqg/dancutracuu_vngoai/kiemtra-congdan
     * ?citizen_pid=000972060001&identify_number=183713829&birth_date=19880101&gender=3&full_name=LÊ THỊ NHUNG
     */
    public function kiemTraTKCDTuCSDLDC() {
        $this->helper->setHttpHeaders('application/json', 200);
        try
        {
            $citizen_pid = replace_bad_char(get_request_var('citizen_pid', ''));
            $identify_number = replace_bad_char(get_request_var('identify_number', ''));
            $birth_date = replace_bad_char(get_request_var('birth_date', ''));
            $gender = replace_bad_char(get_request_var('gender', ''));
            $full_name = replace_bad_char(get_request_var('full_name', ''));

            if((!$identify_number && !$citizen_pid) || !$birth_date || !$gender || !$full_name ) {
                echo json_encode([
                    'status' => 1,
                    'msg' => 'Lỗi thiếu thông tin.'], JSON_UNESCAPED_SLASHES);
                exit;
            }

            $API_DAN_CU = Model\Entity\System\Parameter::fromId('BCA_API_DAN_CU_KTRA_TTCD_TRUE_FALSE')->getValue();
            $API_DAN_CU = $API_DAN_CU ? $API_DAN_CU : 'http://10.159.29.27:7007/ords-kho-dan-cu/csdl_dancuqg/dancutracuu_vngoai/kiemtra-congdan';
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
                'timeout'      => 30
            ));
            $client->setMethod('GET');
            $client->setHeaders(array('Content-Type' => 'application/json'));
            $client->setParameterGet(array(
                'citizen_pid' => $citizen_pid,
                'identify_number'   => $identify_number,
                'birth_date'   => $birth_date,
                'gender'    => $gender,
                'full_name'     => $full_name
            ));
            $client->setUri($API_DAN_CU);
            $response = $client->send();
            if ($response->isSuccess()) {
                $data = $response->getBody();
                if(!empty($data)) {
                    $result = json_decode($data);
                    $result = !empty($result) && !empty($result->RESULT) ? $result->RESULT : 0;
                } else {
                    $result = 0;
                }
                echo json_encode([
                    'status' => 0,
                    'data' => $result,
                    'msg' => 'Thành công.'], JSON_UNESCAPED_SLASHES);
            } else {
                echo json_encode([
                    'status' => 2,
                    'msg' => 'Không tìm thấy thông tin công dân trong hệ thống CSDL Dân cư Quốc Gia'], JSON_UNESCAPED_SLASHES);
            }
            exit;
        } catch(Client\Adapter\Exception\TimeoutException $e) {
            echo json_encode([
                'status' => 1,
                'msg' => 'Lỗi.'], JSON_UNESCAPED_SLASHES);
            exit;
        }
    }

    /**
     * API lấy thông tin công dân từ kho dữ liệu cơ sở lưu trú Cư trú - C06
     * tham khao api/bocongan/callThongTinCDCSLT
     * Method: POST
     *
     * Nếu user chọn trường hợp - nộp với công dân : check đã sso chưa
     * Nếu user chọn trường hợp - nộp với cslt : call api, get thông tin, lưu vào session.
     */
    public function callThongTinCDCSLT() {
        $this->helper->setHttpHeaders('application/json', 200);
        try
        {
            $username = get_request_var('username_cslt');
            $password = get_request_var('pwd_cslt');
            if(!$username || !$password) {
                echo json_encode([
                    'status' => 1,
                    'msg' => 'Không được để trống tên đăng nhập hoặc mật khẩu'], JSON_UNESCAPED_SLASHES);
                exit;
            }
            // check nếu tồn tại session login thì không cần call xác thực.
            $url = Model\Entity\System\Parameter::fromId('BCA_API_XAC_THUC_TK_CSLT_C06')->getValue();
            $TKLT = 'TKLT';
            if(Model\Entity\System\Parameter::fromId('BCA_USERTYPE_XAC_THUC_TK_CSLT_C06')->getValue() != '') {
                $TKLT = Model\Entity\System\Parameter::fromId('BCA_USERTYPE_XAC_THUC_TK_CSLT_C06')->getValue();
            }
            if(!$url) {
                echo json_encode([
                    'status' => 1,
                    'msg' => 'Lỗi chưa cấu hình tham số'], JSON_UNESCAPED_SLASHES);
                exit;
            }

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS =>'{ 
                    "provider": "default",
                    "service": "login_cslt",
                    "type": "ref",
                    "p_user_id": "",
                    "p_user_ip": "",
                    "p_user": "'.$username.'",
                    "user_type": "'.$TKLT.'",
                    "p_password": "'.$password.'"}',
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJzdWIiOiJkdmNfYmNhIiwiaXNzIjoiT0F1dGgifQ.TNZi_RIOy-GnSNhJ7-AlVkSLHeCGbhrSuCfM-spqnRSxxS0FlFM__sw45mFwKEsRB_d6KwOb3Rti5yvDz-hSfA',
                    'Content-Type: application/json'
                ),
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            if (!empty($response)) {

                $result = @json_decode($response) ;
                if($result) {
                    $user = $result->result[0];
                    $convert_user = [
                        'USER' => !empty($user->USER) ? $user->USER : '',
                        'FULL_NAME' => !empty($user->FULL_NAME) ? $user->FULL_NAME : '',
                        'MOBILE' => !empty($user->MOBILE) ? $user->MOBILE : '',
                        'EMAIL' => !empty($user->EMAIL) ? $user->EMAIL : ''
                    ];

                    if(isset($user->RESULT) && $user->RESULT == 'Fail') {
                        echo json_encode([
                            'status' => 2,
                            'data' => [],
                            'msg' => 'Tài khoản sai hoặc chưa được kích hoạt.'], JSON_UNESCAPED_SLASHES);
                    } else {
                        Session::set(TIEP_DAU_NGU_SESSION.'BCA_CU_TRU_TT_LUU_TRU_TK_CSLT', $convert_user);
                        echo json_encode([
                            'status' => 0,
                            'data' => $convert_user,
                            'msg' => 'Thành công.'], JSON_UNESCAPED_SLASHES);
                    }
                } else {
                    echo json_encode([
                        'status' => 3,
                        'msg' => 'Kết nối thất bại.'], JSON_UNESCAPED_SLASHES);
                }
            } else {
                echo json_encode([
                    'status' => 2,
                    'msg' => 'Không tìm thấy thông tin cơ sở lưu trú.'], JSON_UNESCAPED_SLASHES);
            }
            exit;

        } catch(Client\Adapter\Exception\TimeoutException $e) {
            echo json_encode([
                'status' => 1,
                'msg' => 'Lỗi.'], JSON_UNESCAPED_SLASHES);
            exit;
        }
    }

    public function callLePhiHoSo(){
        $this->helper->setHttpHeaders('application/json', 200);
        try
        {
            $orgcode = get_request_var('v_orgcode');
            $bsncode = get_request_var('v_bsncode');
            $curl = curl_init();
            curl_setopt_array($curl, array(
            CURLOPT_URL => '10.165.17.22:7777/portalapi_ext/call?client_id=dvc_bca',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
                "type": "ref",
                "provider": "default",
                "service": "get_fee",
                "p_org_code":"G01.015.001.000",
                "p_business_code":"THUONGTRU_01",
                "user_id":"",
                "user_ip":""
            }',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJzdWIiOiJkdmNfYmNhIiwiaXNzIjoiT0F1dGgifQ.TNZi_RIOy-GnSNhJ7-AlVkSLHeCGbhrSuCfM-spqnRSxxS0FlFM__sw45mFwKEsRB_d6KwOb3Rti5yvDz-hSfA',
                'Content-Type: application/json'
            ),
            ));
            $response = curl_exec($curl);
            curl_close($curl);

            if (!empty($response)) {
                $result = @json_decode($response) ;
                if($result) {
                    if(!empty($result->result) && !empty($result->result[0])){
                        $inf_fee = $result->result[0];
                        $convert_cq = [
                            'MACOQUAN' => !empty($inf_fee->MACOQUAN) ? $inf_fee->MACOQUAN : '',
                            'MATHUTUC' => !empty($inf_fee->MATHUTUC) ? $inf_fee->MATHUTUC : '',
                            'maTKThuHuong' => !empty($inf_fee->TAIKHOAN) ? $inf_fee->TAIKHOAN : '',
                            'tenTKThuHuong' => !empty($inf_fee->TENNGUOINHAN) ? $inf_fee->TENNGUOINHAN : '',
                            'MANGANHANG' => !empty($inf_fee->MANGANHANG) ? $inf_fee->MANGANHANG : '',
                            'TENNGANHANG' => !empty($inf_fee->TENNGANHANG) ? $inf_fee->TENNGANHANG : '',
                            'PHI' => !empty($inf_fee->PHI) ? $inf_fee->PHI : '',
                            'MACQ_DVCQG' => !empty($inf_fee->MACQ_DVCQG) ? $inf_fee->MACQ_DVCQG : ''
                        ];
                        if(isset($inf_fee->RESULT) && $inf_fee->RESULT == 'Fail') {
                            echo json_encode([
                                'status' => 2,
                                'data' => [],
                                'msg' => 'Không tồn tại phí.'], JSON_UNESCAPED_SLASHES);
                        } else {
                            echo json_encode([
                                'status' => 0,
                                'data' => $convert_cq,
                                'msg' => 'Thành công.'], JSON_UNESCAPED_SLASHES);
                            return $convert_cq;
                        }
                    }else{
                        echo json_encode([
                            'status' => 0,
                            'msg' => 'Không tồn tại phí.'], JSON_UNESCAPED_SLASHES);
                    }
                    
                } else {
                    echo json_encode([
                        'status' => 3,
                        'msg' => 'Kết nối thất bại.'], JSON_UNESCAPED_SLASHES);
                }
            }
        }catch(Client\Adapter\Exception\TimeoutException $e) {
            echo json_encode([
                'status' => 1,
                'msg' => 'Lỗi.'], JSON_UNESCAPED_SLASHES);
            exit;
        }
    }

    public function coreCallThongTinCDCSLT($username = '', $password = '') {
        // check nếu tồn tại session login thì không cần call xác thực.
        $url = Model\Entity\System\Parameter::fromId('BCA_API_XAC_THUC_TK_CSLT_C06')->getValue();
        $TKLT = 'TKLT';
        if(Model\Entity\System\Parameter::fromId('BCA_USERTYPE_XAC_THUC_TK_CSLT_C06')->getValue() != '') {
            $TKLT = Model\Entity\System\Parameter::fromId('BCA_USERTYPE_XAC_THUC_TK_CSLT_C06')->getValue();
        }
        if(!$url || !$username || !$password) {
            return false;
        }

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{ 
                    "provider": "default",
                    "service": "login_cslt",
                    "type": "ref",
                    "p_user_id": "",
                    "p_user_ip": "",
                    "p_user": "'.$username.'",
                    "user_type": "'.$TKLT.'",
                    "p_password": "'.$password.'"}',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJzdWIiOiJkdmNfYmNhIiwiaXNzIjoiT0F1dGgifQ.TNZi_RIOy-GnSNhJ7-AlVkSLHeCGbhrSuCfM-spqnRSxxS0FlFM__sw45mFwKEsRB_d6KwOb3Rti5yvDz-hSfA',
                'Content-Type: application/json'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        if (!empty($response)) {
            $result = @json_decode($response) ;
            if($result) {
                $user = $result->result[0];
                $convert_user = [
                    'USER' => !empty($user->USER) ? $user->USER : '',
                    'FULL_NAME' => !empty($user->FULL_NAME) ? $user->FULL_NAME : '',
                    'MOBILE' => !empty($user->MOBILE) ? $user->MOBILE : '',
                    'EMAIL' => !empty($user->EMAIL) ? $user->EMAIL : '',
                ];
                if(isset($user->RESULT) && $user->RESULT == 'Fail') {
                    return false;
                } else {
                    return $convert_user ;
                }
            } else {
                return false;
            }
        }
        return false;
    }

    // Dữ liệu session từ function callThongTinCDCSLT
    public function sessionCSLT() {
        if(Session::get(TIEP_DAU_NGU_SESSION.'BCA_CU_TRU_TT_LUU_TRU_TK_CSLT')) {
            echo json_encode([
                'status' => 1,
                'data' => Session::get(TIEP_DAU_NGU_SESSION.'BCA_CU_TRU_TT_LUU_TRU_TK_CSLT')
            ], JSON_UNESCAPED_SLASHES);
        } else {
            echo json_encode([
                'status' => 1,
                'data' => [
                    'USER' => '',
                    'FULL_NAME' => '',
                    'MOBILE' => '',
                    'EMAIL' => ''
                ]
            ], JSON_UNESCAPED_SLASHES);
        }
        exit;
    }

    /* tham khao api/boconganthutuc/getSesionCDLogin */
    public function getSesionCDLogin() {
        $this->helper->setHttpHeaders('application/json', 200);

        $thong_tin_cd = Session::get(TIEP_DAU_NGU_SESSION . 'DU_LIEU_CONG_DAN');
        if(!$thong_tin_cd) {
            echo json_encode([
                'status' => 0,
                'msg' => 'Lỗi chưa đăng nhập hoặc không có số CMND / CCCD.'], JSON_UNESCAPED_SLASHES);
            exit;
        }
        echo json_encode([
            'status' => 1,
            'data' => ['P_DI_DONG' => $thong_tin_cd->P_DI_DONG]], JSON_UNESCAPED_SLASHES);
        exit;
    }

    public function checkThoiGianTamVang() {
        $this->helper->setHttpHeaders('application/json', 200);
        try
        {
            $curl = curl_init();
            // check nếu tồn tại session login thì không cần call xác thực.
//            $url = $this->getURLC06('c06_CHECKTGTAMVANG');
            $url = Model\Entity\System\Parameter::fromId('BCA_API_c06_CHECKTGTAMVANG')->getValue();
            if(!$url) {
                echo json_encode([
                    'status' => 1,
                    'msg' => 'Lỗi chưa cấu hình tham số'], JSON_UNESCAPED_SLASHES);
                exit;
            }
            if(Model\Entity\System\Parameter::fromId('BCA_test_dump')->getValue() == 1) {
                echo '-1sss-';
                var_dump($url);
            }
            $p_from_date = get_request_var('p_from_date');
            $p_to_date = get_request_var('p_to_date');
            if(Model\Entity\System\Parameter::fromId('BCA_test_dump')->getValue() == 1) {
                echo '-1-';
                var_dump($p_from_date);
                var_dump($p_to_date);
            }
            $thong_tin_cd = Session::get(TIEP_DAU_NGU_SESSION . 'DU_LIEU_CONG_DAN');
            if(!empty($thong_tin_cd)) {
                $congdan_logined = (new Package\BCA_DICH_VU_CONG())->GET_TECHID_SSO_QUOC_GIA([
                    'P_MA_CONG_DAN' => $thong_tin_cd->P_MA_CONG_DAN
                ]);
                $TECHID_VNCONNECT_SSO = $congdan_logined->TECHID_VNCONNECT_SSO;
            }
            if(Model\Entity\System\Parameter::fromId('BCA_test_dump')->getValue() == 1) {
                echo '-2-';
                var_dump($TECHID_VNCONNECT_SSO);
            }

            if(!empty($TECHID_VNCONNECT_SSO)) {
                curl_setopt_array($curl, array(
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS =>'{ 
                    "provider": "dancuportal",
                    "service": "check_trung_time_absence",
                    "type": "json",
                    "p_tech_id": "'.$TECHID_VNCONNECT_SSO.'",
                    "p_from_date": "'.$p_from_date.'",
                    "p_to_date": "'.$p_to_date.'"
                    }',
                    CURLOPT_HTTPHEADER => array(
                        'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJzdWIiOiJkdmNfYmNhIiwiaXNzIjoiT0F1dGgifQ.TNZi_RIOy-GnSNhJ7-AlVkSLHeCGbhrSuCfM-spqnRSxxS0FlFM__sw45mFwKEsRB_d6KwOb3Rti5yvDz-hSfA',
                        'Content-Type: application/json'
                    ),
                ));

                $response = curl_exec($curl);
                curl_close($curl);
                if(Model\Entity\System\Parameter::fromId('BCA_test_dump')->getValue() == 1) {
                    echo '-3-';
                    var_dump($response);
                }
                if (!empty($response)) {
                    $result = @json_decode($response);
                    if($result) {
                        if($result->MSG_CODE == 1) {
                            echo json_encode([
                                'status' => 0,
                                'data' => [],
                                'msg' => 'Hợp lệ.'], JSON_UNESCAPED_SLASHES);
                            exit;
                        }
                        echo json_encode([
                            'status' => 2,
                            'msg' => 'Không hợp lệ.'], JSON_UNESCAPED_SLASHES);
                        exit;
                    } else {
                        echo json_encode([
                            'status' => 3,
                            'msg' => 'Lỗi kết nối được.'], JSON_UNESCAPED_SLASHES);
                        exit;
                    }
                }
            }
            echo json_encode([
                'status' => 2,
                'msg' => 'Không hợp lệ.'], JSON_UNESCAPED_SLASHES);
            exit;
        } catch(Client\Adapter\Exception\TimeoutException $e) {
            echo json_encode([
                'status' => 1,
                'msg' => 'Lỗi.'], JSON_UNESCAPED_SLASHES);
            exit;
        }
    }

    public function getURLC06($type = '') {
        if(Model\Entity\System\Parameter::fromId('BCA_C06_LIST_API')->getValue() != '' && $type != '') {
            $url = getThamSoArray(Model\Entity\System\Parameter::fromId('BCA_C06_LIST_API')->getValue());
            if(!empty($url[$type])) {
                return $url[$type];
            }
        }

        return '';
    }

    /************* C08 *************/

    public function getTokenC08() {
        $config = getThamSoArray(Model\Entity\System\Parameter::fromId('BCA_C08_API_CONFIG')->getValue());
        if(Model\Entity\System\Parameter::fromId('BCA_test_dump_C08')->getValue() == 1) {
            echo '- 1-';
            var_dump($config);
        }
        $username = !empty($config['username']) ? $config['username'] : '';
        $password = !empty($config['password']) ? $config['password'] : '';
        $url = Model\Entity\System\Parameter::fromId('BCA_C08_API_CONFIG_URL_LOGIN')->getValue();
        $url = $url ? $url : 'https://172.16.1.14:8089/dvcsv/auth/login';
        if(Model\Entity\System\Parameter::fromId('BCA_test_dump_C08')->getValue() == 1) {
            echo '- 1111-'; var_dump($username); var_dump($password); var_dump($url);
        }
        if($url && $username && $password) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS =>'{
                    "username": "'.$username.'",
                    "password": "'.$password.'"
                }',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
//                    'Cookie: BIGipServerws-dvcbca-https.app~ws-dvcbca-https_pool=39456778.39199.0000'
                ),
                CURLOPT_SSL_VERIFYPEER => 0 ,
                CURLOPT_SSL_VERIFYHOST => 0
            ));

            $response = curl_exec($curl);
            curl_close($curl);

            if(Model\Entity\System\Parameter::fromId('BCA_test_dump_C08')->getValue() == 1) {
                echo '- 2-';
                var_dump($response);
                var_dump(array(
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS =>'{
                    "username": "'.$username.'",
                    "password": "'.$password.'"
                }',
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/json',
//                        'Cookie: BIGipServerws-dvcbca-https.app~ws-dvcbca-https_pool=39456778.39199.0000'
                    ),
                    CURLOPT_SSL_VERIFYPEER => 0 ,
                    CURLOPT_SSL_VERIFYHOST => 0
                ));
            }
            if (!empty($response)) {
                $result = json_decode($response);
                if(Model\Entity\System\Parameter::fromId('BCA_test_dump_C08')->getValue() == 1) {
                    echo '- 3-';
                    var_dump($result);
                }
                if(!empty($result) && !empty($result->token)) {
                    return $result->token;
                }
                return false;
            }
            return false;
        }
        return false;
    }

    public function checkExist($value = '') {
        if(isset($value) && $value !== '') {
           return true;
        }
        return false;
    }

    public function traCuuThongTinDKXC08($params = []) {
        if (empty($params)) {
            $bien_so = get_request_var('bien_so');
            $so_khung = get_request_var('so_khung');
            $loai_xe = get_request_var('loai_xe');
            $mau_bien = get_request_var('mau_bien');
        } else {
            $bien_so = $params['bien_so'];
            $so_khung = $params['so_khung'];
            $loai_xe = $params['loai_xe'];
            $mau_bien = $params['mau_bien'];
        }
        if(!$this->checkExist($bien_so) || !$this->checkExist($so_khung)
            || !$this->checkExist($loai_xe) || !$this->checkExist($mau_bien)) {
            echo $this->resultEncode(3, [], 'Thiếu thông tin tra cứu.');
            exit;
        }
        if(Model\Entity\System\Parameter::fromId('BCA_test_dump_C08')->getValue() == 1) {
            echo '- 1-'; var_dump($bien_so);var_dump($so_khung);var_dump($loai_xe);var_dump($mau_bien);
        }
        //http://IP-Service/dvcsv/tracuu/ttxe/{bien_so}/{so_khung}/{loai_xe}/{mau_bien}
        $url = Model\Entity\System\Parameter::fromId('BCA_C08_API_CONFIG_url_TracuuTTX')->getValue();
        $url = $url ? $url : 'https://172.16.1.14:8089/dvcsv/tracuu/ttxe';
        if($url) {
            // Get token:
            $new_token = $this->getTokenC08();
            if(Model\Entity\System\Parameter::fromId('BCA_test_dump_C08')->getValue() == 1) {
                echo '- 2-'; var_dump($new_token); var_dump($url.'/'.$bien_so.'/'.$so_khung.'/'.$loai_xe.'/'.$mau_bien);
            }
            if(!empty($new_token) && is_string($new_token)) {
                $urlApi = $url.'/'.$bien_so.'/'.$so_khung.'/'.$loai_xe.'/'.$mau_bien;

                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => $urlApi,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    CURLOPT_HTTPHEADER => array(
                        'Authorization: Bearer ' . $new_token,
//                        'Cookie: BIGipServerws-dvcbca-https.app~ws-dvcbca-https_pool=39456778.39199.0000'
                    ),
                    CURLOPT_SSL_VERIFYPEER => 0 ,
                    CURLOPT_SSL_VERIFYHOST => 0
                ));
                $response = curl_exec($curl);
                curl_close($curl);

                if(Model\Entity\System\Parameter::fromId('BCA_test_dump_C08')->getValue() == 1) {
                    echo '- 3-'; var_dump($response);
                    var_dump( array(
                        CURLOPT_URL => $urlApi,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'GET',
                        CURLOPT_HTTPHEADER => array(
                            'Authorization: Bearer ' . $new_token,
//                        'Cookie: BIGipServerws-dvcbca-https.app~ws-dvcbca-https_pool=39456778.39199.0000'
                        ),
                        CURLOPT_SSL_VERIFYPEER => 0 ,
                        CURLOPT_SSL_VERIFYHOST => 0
                    ));
                }

                if (!empty($response)) {
                    $result = json_decode($response);
                    if(Model\Entity\System\Parameter::fromId('BCA_test_dump_C08')->getValue() == 1) {
                        echo '- 4-'; var_dump($result);
                    }
                    if(!empty($result)) {
                        echo $this->resultEncode(0, $result, 'Thông tin xe.');
                        exit;
                    }
                    echo $this->resultEncode(2, [], 'Thông tin xe rỗng.');
                    exit;
                }
                echo $this->resultEncode(2, [], 'Không lấy được Thông tin xe.');
                exit;
            }
            echo $this->resultEncode(1, [], 'Không lấy được token');
            exit;
        }

        echo $this->resultEncode(1, [], 'Chưa cấu hình tham số');
        exit;
    }

    public function traCuuThongTinLPTBC08($params = []) {
        if (empty($params)) {
            $ma_lptb = get_request_var('ma_lptb');
        } else {
            $ma_lptb = !empty($params['ma_lptb']) ? $params['ma_lptb'] : '';
        }
        if(!$this->checkExist($ma_lptb)) {
            echo $this->resultEncode(3, [], 'Thiếu dữ liệu mã lệ phí trước bạ');
            exit;
        }
        if(Model\Entity\System\Parameter::fromId('BCA_test_dump_C08')->getValue() == 1) {
            echo '- 1-'; var_dump($ma_lptb);
        }
        //http://IP-Service/dvcsv/tracuu/lptb/{ma_lptb}
        $url = Model\Entity\System\Parameter::fromId('BCA_C08_API_CONFIG_url_TracuuThueLPTB')->getValue();
        if($url ) {
            // Get token:
            $new_token = $this->getTokenC08();
            if(Model\Entity\System\Parameter::fromId('BCA_test_dump_C08')->getValue() == 1) {
                echo '- 2-'; var_dump($new_token); var_dump($url.'/'.$ma_lptb);
            }
            if(!empty($new_token) && is_string($new_token)) {
                $urlApi = $url.'/'.$ma_lptb;
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => $urlApi,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    CURLOPT_HTTPHEADER => array(
                        'Authorization: Bearer ' . $new_token,
                    ),
                    CURLOPT_SSL_VERIFYPEER => 0 ,
                    CURLOPT_SSL_VERIFYHOST => 0
                ));
                $response = curl_exec($curl);
                curl_close($curl);
                if(Model\Entity\System\Parameter::fromId('BCA_test_dump_C08')->getValue() == 1) {
                    echo '- 3-'; var_dump($response);
                }
                if (!empty($response)) {
                    $result = json_decode($response);
                    if(Model\Entity\System\Parameter::fromId('BCA_test_dump_C08')->getValue() == 1) {
                        echo '- 4-'; var_dump($result);
                    }
                    if(!empty($result)) {
                        echo $this->resultEncode(0, $result, 'Thông tin xe.');
                        exit;
                    }
                    echo $this->resultEncode(2, [], 'Thông tin xe rỗng.');
                    exit;
                }
                echo $this->resultEncode(2, [], 'Không lấy được Thông tin xe.');
                exit;
            }
            echo $this->resultEncode(1, [], 'Không lấy được token');
            exit;
        }
        echo $this->resultEncode(1, [], 'Chưa cấu hình tham số');
        exit;
    }

    public function traCuuThongTinDangKiemC08() {
        if(!$this->checkExist(get_request_var('ma_phieu'))) {
            echo $this->resultEncode(3, [], 'Thiếu dữ liệu mã phiếu xuất xưởng');
            exit;
        }
        $ma_phieu = get_request_var('ma_phieu') ;
        if(Model\Entity\System\Parameter::fromId('BCA_test_dump_C08')->getValue() == 1) {
            echo '- 1-'; var_dump($ma_phieu);
        }
        //http://IP-Service/dvcsv/tracuu/lptb/{ma_phieu}
        $url = Model\Entity\System\Parameter::fromId('BCA_C08_API_CONFIG_url_TracuuDangKiem')->getValue();
        if($url ) {
            // Get token:
            $new_token = $this->getTokenC08();
            if(Model\Entity\System\Parameter::fromId('BCA_test_dump_C08')->getValue() == 1) {
                echo '- 2-'; var_dump($new_token); var_dump($url.'/'.$ma_phieu);
            }
            if(!empty($new_token) && is_string($new_token)) {
                $config = getThamSoArray(Model\Entity\System\Parameter::fromId('BCA_C08_API_CONFIG')->getValue());
                $username = !empty($config['username']) ? $config['username'] : '';
                $password = !empty($config['password']) ? $config['password'] : '';
                if(Model\Entity\System\Parameter::fromId('BCA_test_dump_C08')->getValue() == 1) {
                    echo '- 1111-'; var_dump($username); var_dump($password);
                }

                $urlApi = $url.'/'.$ma_phieu;
                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => $urlApi,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    CURLOPT_POSTFIELDS =>'{
                    "username": "'.$username.'",
                    "password": "'.$password.'"
                    }',
                    CURLOPT_HTTPHEADER => array(
                        'Authorization: Bearer ' . $new_token,
                        'Content-Type: application/json',
                    ),
                    CURLOPT_SSL_VERIFYPEER => 0 ,
                    CURLOPT_SSL_VERIFYHOST => 0
                ));

                $response = curl_exec($curl);
                curl_close($curl);
                if(Model\Entity\System\Parameter::fromId('BCA_test_dump_C08')->getValue() == 1) {
                    echo '- 3-'; var_dump($response);
                }
                if (!empty($response)) {
                    $result = json_decode($response);
                    if(Model\Entity\System\Parameter::fromId('BCA_test_dump_C08')->getValue() == 1) {
                        echo '- 4-'; var_dump($result);
                    }
                    if(!empty($result)) {
                        echo $this->resultEncode(0, $result, 'Thông tin xe.');
                        exit;
                    }
                    echo $this->resultEncode(2, [], 'Thông tin xe rỗng.');
                    exit;
                }
                echo $this->resultEncode(2, [], 'Không lấy được Thông tin xe.');
                exit;
            }
            echo $this->resultEncode(1, [], 'Không lấy được token');
            exit;
        }
        echo $this->resultEncode(1, [], 'Chưa cấu hình tham số');
        exit;
    }

    public function traCuuThongTinHaiQuanC08() {
        if(!$this->checkExist(get_request_var('so_khung'))) {
            echo $this->resultEncode(3, [], 'Thiếu dữ liệu số khung');
            exit;
        }
        $so_khung = get_request_var('so_khung') ;
        if(Model\Entity\System\Parameter::fromId('BCA_test_dump_C08')->getValue() == 1) {
            echo '- 1-'; var_dump($so_khung);
        }
        //http://IP-Service/dvcsv/tracuu/lptb/{so_khung}
        $url = Model\Entity\System\Parameter::fromId('BCA_C08_API_CONFIG_url_TracuuHaiQuan')->getValue();
        if($url ) {
            // Get token:
            $new_token = $this->getTokenC08();
            if(Model\Entity\System\Parameter::fromId('BCA_test_dump_C08')->getValue() == 1) {
                echo '- 2-'; var_dump($new_token); var_dump($url.'/'.$so_khung);
            }
            if(!empty($new_token) && is_string($new_token)) {
                $config = getThamSoArray(Model\Entity\System\Parameter::fromId('BCA_C08_API_CONFIG')->getValue());
                $username = !empty($config['username']) ? $config['username'] : '';
                $password = !empty($config['password']) ? $config['password'] : '';
                if(Model\Entity\System\Parameter::fromId('BCA_test_dump_C08')->getValue() == 1) {
                    echo '- 1111-'; var_dump($username); var_dump($password);
                }

                $urlApi = $url.'/'.$so_khung;
                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => $urlApi,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    CURLOPT_POSTFIELDS =>'{
                    "username": "'.$username.'",
                    "password": "'.$password.'"
                    }',
                    CURLOPT_HTTPHEADER => array(
                        'Authorization: Bearer ' . $new_token,
                        'Content-Type: application/json',
                    ),
                    CURLOPT_SSL_VERIFYPEER => 0 ,
                    CURLOPT_SSL_VERIFYHOST => 0
                ));

                $response = curl_exec($curl);
                curl_close($curl);
                if(Model\Entity\System\Parameter::fromId('BCA_test_dump_C08')->getValue() == 1) {
                    echo '- 3-'; var_dump($response);
                }
                if (!empty($response)) {
                    $result = json_decode($response);
                    if(Model\Entity\System\Parameter::fromId('BCA_test_dump_C08')->getValue() == 1) {
                        echo '- 4-'; var_dump($result);
                    }
                    if(!empty($result)) {
                        echo $this->resultEncode(0, $result, 'Thông tin xe.');
                        exit;
                    }
                    echo $this->resultEncode(2, [], 'Thông tin xe rỗng.');
                    exit;
                }
                echo $this->resultEncode(2, [], 'Không lấy được Thông tin xe.');
                exit;
            }
            echo $this->resultEncode(1, [], 'Không lấy được token');
            exit;
        }
        echo $this->resultEncode(1, [], 'Chưa cấu hình tham số');
        exit;
    }

    public function resultEncode($status = '1', $data = [], $msg = '') {
        return json_encode([
            'status' => $status,
            'data' => $data,
            'msg' => $msg], JSON_UNESCAPED_SLASHES);
    }

    function cleanString($string) {
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
        return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
    }

    function testGetTokenC08() {

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://172.16.1.14:8089/dvcsv/auth/login',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
                "username": "v01_user",
                "password": "dvc@2022"
            }',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
//                    'Cookie: BIGipServerws-dvcbca-https.app~ws-dvcbca-https_pool=39456778.39199.0000'
            ),
            CURLOPT_SSL_VERIFYPEER => 0 ,
            CURLOPT_SSL_VERIFYHOST => 0
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        dump_die($response);
        return 1;
    }

    function testGetDKX() {
        $token_test = get_request_var('token_test');
        $token_test = $token_test ? $token_test : "eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOiJ2MDFfdXNlciIsImV4cCI6MTY0NDQwOTc3MSwiaWF0IjoxNjQ0MzkxNzcxfQ.ikPsygArURVn-wMw4-W3LNfuMOdzkAg7iZFjoPhtDiHJOAtCidUbMxkrl4AYh3tyqxmTYes-IcXv_IsCNyxq3A";
        var_dump($token_test);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://172.16.1.14:8089/dvcsv/tracuu/ttxe/80A00887/JGHJGHJGHJGHJGHJGHJ/1/1',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $token_test,
                'Cookie: BIGipServerws-dvcbca-https.app~ws-dvcbca-https_pool=39456778.39199.0000'
            ),
            CURLOPT_SSL_VERIFYPEER => 0 ,
            CURLOPT_SSL_VERIFYHOST => 0
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        dump_die($response);

        return 1;
    }

    function testGetLPTB() {
        $token_test = get_request_var('token_test');
        $token_test = $token_test ? $token_test :  'eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOiJ2MDFfdXNlciIsImV4cCI6MTY0NDQwOTc3MSwiaWF0IjoxNjQ0MzkxNzcxfQ.ikPsygArURVn-wMw4-W3LNfuMOdzkAg7iZFjoPhtDiHJOAtCidUbMxkrl4AYh3tyqxmTYes-IcXv_IsCNyxq3A';
        var_dump($token_test);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://172.16.1.14:8089/dvcsv/tracuu/lptb/1102021000274934',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '  . $token_test
            ),
            CURLOPT_SSL_VERIFYPEER => 0 ,
            CURLOPT_SSL_VERIFYHOST => 0
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        dump_die($response);

        return 1;
    }

    function testGetDANGKIEM() {
        $token_test = get_request_var('token_test');
        $token_test = $token_test ? $token_test :  'eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOiJ2MDFfdXNlciIsImV4cCI6MTY0NDQwOTc3MSwiaWF0IjoxNjQ0MzkxNzcxfQ.ikPsygArURVn-wMw4-W3LNfuMOdzkAg7iZFjoPhtDiHJOAtCidUbMxkrl4AYh3tyqxmTYes-IcXv_IsCNyxq3A';
        var_dump($token_test);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://172.16.1.14:8089/dvcsv/tracuu/dkiem/AS0390201',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_POSTFIELDS =>'{
            "username":"v01_user",
            "password":"dvc@2022"
        }
        ',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '  . $token_test,
                'Content-Type: application/json',
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        dump_die($response);

        return 1;
    }

    /************* END C08 *************/
    public function traCuuThongTinCongDanC06(){
        try
        {
            $thong_tin_cd = Session::get(TIEP_DAU_NGU_SESSION . 'DU_LIEU_CONG_DAN');
            if((!$thong_tin_cd || !$thong_tin_cd->P_CMND_CONG_DAN || !$thong_tin_cd->P_NGAY_SINH) && Model\Entity\System\Parameter::fromId('BCA_API_C06_CHECK_DANG_NHAP')->getValue() == 0) {
                echo json_encode([
                    'status' => 1,
                    'msg' => 'Lỗi chưa đăng nhập hoặc không có thông tin CCCD/ ngày sinh.'], JSON_UNESCAPED_SLASHES);
                exit;
            }

            $MA_DVC = Model\Entity\System\Parameter::fromId('BCA_API_C06_MA_DVC')->getValue();
            $MA_TICH_HOP = Model\Entity\System\Parameter::fromId('BCA_API_C06_MA_TICH_HOP')->getValue();
            $MA_YEU_CAU = Model\Entity\System\Parameter::fromId('BCA_API_C06_MA_YEU_CAU')->getValue();
            $MA_CAN_BO = Model\Entity\System\Parameter::fromId('BCA_API_C06_MA_CAN_BO')->getValue();

            $CMND_CCCD_TEST = Model\Entity\System\Parameter::fromId('BCA_API_DAN_CU_C06_TEST_CMND_CCCD')->getValue();
            $CMND_CCCD = $CMND_CCCD_TEST ? $CMND_CCCD_TEST : $thong_tin_cd->P_CMND_CONG_DAN ;

            $NGAY_SINH_TEST = Model\Entity\System\Parameter::fromId('BCA_API_DAN_CU_C06_TEST_NGAY_SINH')->getValue();
            $NGAY_SINH = $NGAY_SINH_TEST ? $NGAY_SINH_TEST : date("Ymd", strtotime($thong_tin_cd->P_NGAY_SINH));

            $HO_VA_TEN_TEST = Model\Entity\System\Parameter::fromId('BCA_API_DAN_CU_C06_TEST_HO_VA_TEN')->getValue();
            $HO_VA_TEN_TEST = $HO_VA_TEN_TEST ? $HO_VA_TEN_TEST : $thong_tin_cd->P_TEN_CONG_DAN ;
            $HO_VA_TEN = (string)strtoupper(trim($HO_VA_TEN_TEST));

            if(Model\Entity\System\Parameter::fromId('BCA_test_dump_C06')->getValue() == 1) {
                echo '<br>CMND_CCCD_TEST<br>'; var_dump([
                    'CMND_CCCD_TEST' => $CMND_CCCD_TEST,
                    'CMND_CCCD' => $CMND_CCCD,
                    'NGAY_SINH' => $NGAY_SINH,
                    'HO_VA_TEN' => $HO_VA_TEN,
                ]); echo '<br>';
            }

            $url = Model\Entity\System\Parameter::fromId('BCA_C06_API_CONFIG_url_TraCuuCongDan')->getValue();
            $sv_url = Model\Entity\System\Parameter::fromId('BCA_C06_API_CONFIG_url')->getValue();
            if (empty($url)) {
                return json_encode(['status' => 'error', 'message' => 'Chưa cấu hình tham số hệ thống !']);
            } else {
                $soapRequestCMND = strlen((string)$CMND_CCCD) === 9 ? "<dan:SoCMND>".$CMND_CCCD."</dan:SoCMND>" : "<dan:SoDinhDanh>".$CMND_CCCD."</dan:SoDinhDanh>";
                $soapRequestNgaySinh = "<dan:NgayThangNam>".$NGAY_SINH."</dan:NgayThangNam>"; // tạm

                $username = \Model\Entity\System\Parameter::fromId('C06_API_TRA_CUU_CONG_DAN_USER')->getValue();
                $password = \Model\Entity\System\Parameter::fromId('C06_API_TRA_CUU_CONG_DAN_PASSWORD')->getValue();

                $date = new DateTime();
                $TIMESTAMP_TEST = Model\Entity\System\Parameter::fromId('BCA_API_C06_KT_TIMESTAMP_TEST')->getValue();
                $timestamp = $TIMESTAMP_TEST ? $TIMESTAMP_TEST : $date->getTimestamp();

                $password_TEST = Model\Entity\System\Parameter::fromId('BCA_API_C06_KT_HASHPASSWORD')->getValue();
                $password = $password_TEST ? $password_TEST : hash('sha256', $sv_url . $username . $timestamp . $password);
                // SHA256(sv_url+username+ timestamp+secretkey)

                if(Model\Entity\System\Parameter::fromId('BCA_test_dump_C06')->getValue() == 1) {
                    echo '<br>timestamp<br>'; var_dump([
                        'timestamp' => $timestamp,
                        'password' => $password,
                    ]); echo '<br>';
                }

                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL =>  $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS =>'<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" 
                                    xmlns:dan="http://dancuquocgia.bca">">
                                    <soapenv:Header/>
                                    <soapenv:Body>
                                        <dan:TraCuuThongTinCongDan xmlns:dan="http://dancuquocgia.bca">
                                        <dan:MaYeuCau>'.$MA_YEU_CAU.'</dan:MaYeuCau>
                                            <dan:MaDVC>'.$MA_DVC.'</dan:MaDVC>
                                            <dan:MaTichHop>'.$MA_TICH_HOP.'</dan:MaTichHop>
                                            <dan:MaCanBo>'.$MA_CAN_BO.'</dan:MaCanBo>  
                                            '.$soapRequestCMND.'
                                            <dan:HoVaTen>'.$HO_VA_TEN.'</dan:HoVaTen>
                                            <dan:NgayThangNamSinh>
                                                '.$soapRequestNgaySinh.'
                                            </dan:NgayThangNamSinh>
                                        </dan:TraCuuThongTinCongDan>
                                    </soapenv:Body>
                                </soapenv:Envelope>',
                    CURLOPT_HTTPHEADER => array(
                        'timestamp: ' . $timestamp,
                        'Content-Type: application/xml',
                    ),
                    CURLOPT_USERPWD =>  $username . ":" . $password
                ));

                $response = curl_exec($curl);
                curl_close($curl);

                if(Model\Entity\System\Parameter::fromId('BCA_test_dump_C06')->getValue() == 1) {
                    echo '<br>response<br>'; var_dump($response); echo "<br>";
                }

                if(empty($response)) {
                    echo $this->resultEncode(2, [], 'Không kết nối được');
                    exit;
                } else {
                    $response1 = str_replace("ns1:","",$response);
                    $response2 = str_replace("soapenv:","",$response1);
                    $response3 = str_replace("<?xml version=\"1.0\" encoding=\"UTF-8\"?>","",$response2);
                    $xml=simplexml_load_string($response3);
                    $json = json_encode($xml);
                    $array = json_decode($json,TRUE);
                    if(!empty($array)) {
                        echo $this->resultEncode(0, $array, 'Thông tin công dân.');
                        exit;
                    }

                    echo $this->resultEncode(1, $array, 'Không có thông tin công dân');
                    exit;
                }
            }
        } catch(Client\Adapter\Exception\TimeoutException $e) {
            echo json_encode([
                'status' => 2,
                'msg' => 'Lỗi.'], JSON_UNESCAPED_SLASHES);
            exit;
        }
    }

    /************* TRANGND THÊM HÀM LẤY THÔNG TIN ĐĂNG NHẬP( CON DẤU ) ********************/

    public function getSessionLoginCD()
    {
        $this->helper->setHttpHeaders('application/json', 200);
        $taiKhoanCongDan = new \Model\CongDan();
        if(count($taiKhoanCongDan->getSessionData()->getIterator()) > 0) {
            echo json_encode([
                'status' => 1,
                'data' => $taiKhoanCongDan->getSessionData()->getIterator()
            ], JSON_UNESCAPED_SLASHES);
            exit;
        }

        echo json_encode([
            'status' => 0,
            'msg' => 'Tài khoản chưa đăng nhập.'], JSON_UNESCAPED_SLASHES);
        exit;
    }
    /***********  END CON DẤU  ************/
}
