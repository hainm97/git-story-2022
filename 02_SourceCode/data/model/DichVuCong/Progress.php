<?php

namespace Model\DichVuCong;

use Model\DichVuCong\Applier;
use Model\DichVuCong\Progress\Step;
use Nth\Html\Node;
use Nth\DataAccess\AbstractList;

class Progress extends AbstractList {
    
    const DICH_VU_CONG_NEW = 1;
    const DICH_VU_CONG_STEP = 2;
    const DICH_VU_CONG_QTI = 3;
    const DICH_VU_CONG_BCA_2B = 4;
    const DICH_VU_CONG_BCA_3B = 5;
    const DICH_VU_CONG_1B = 6; // gộp tất cả thông tin trên cùng 1 màn hình (bao gồm cả lệ phí)
    const DICH_VU_CONG_BCA_NEW = 7; //thực hiện quy trình BCA đủ các bước
    private $applier;
    
    public function __construct(Applier $applier, $activeStepId = null, $key = 0) {
        if (self::DICH_VU_CONG_NEW == $key){
            parent::__construct([
                new Step(1, 'Thủ tục', sprintf('%sdich-vu-cong/tiep-nhan-online/chon-thu-tuc-nop-ho-so', SITE_ROOT)),
                new Step(2, 'Thông tin người nộp', sprintf('%sdich-vu-cong/tiep-nhan-online/nhap-thong-tin-nguoi-nop-ho-so?sid=%s', SITE_ROOT, $applier->getId())),
                new Step(3, 'Thông tin hồ sơ', sprintf('%sdich-vu-cong/tiep-nhan-online/nhap-thong-tin-ho-so?sid=%s', SITE_ROOT, $applier->getId())),
                new Step(4, 'Lệ phí hồ sơ', sprintf('%sdich-vu-cong/tiep-nhan-online/nhap-le-phi-ho-so?sid=%s', SITE_ROOT, $applier->getId())),
                new Step(5, 'Nộp hồ sơ', sprintf('%sdich-vu-cong/tiep-nhan-online/xac-nhan-thong-tin-nop?sid=%s', SITE_ROOT, $applier->getId()))
            ]);
        }
        else if(self::DICH_VU_CONG_STEP == $key)
        {
             parent::__construct([
                new Step(1, 'Thủ tục', sprintf('%sdich-vu-cong/dich-vu-cong/chon-thu-tuc-nop-ho-so', SITE_ROOT)),
                new Step(2, 'Thông tin người nộp', sprintf('%sdich-vu-cong/dich-vu-cong/nhap-thong-tin-nguoi-nop-ho-so?sid=%s', SITE_ROOT, $applier->getId())),
                new Step(3, 'Thông tin hồ sơ', sprintf('%sdich-vu-cong/dich-vu-cong/nhap-thong-tin-ho-so?sid=%s', SITE_ROOT, $applier->getId())),
                new Step(4, 'Lệ phí hồ sơ', sprintf('%sdich-vu-cong/dich-vu-cong/nhap-le-phi-ho-so?sid=%s', SITE_ROOT, $applier->getId())),
                new Step(5, 'Nộp hồ sơ', sprintf('%sdich-vu-cong/dich-vu-cong/xac-nhan-thong-tin-nop?sid=%s', SITE_ROOT, $applier->getId()))
            ]);
        }
        else if (self::DICH_VU_CONG_QTI == $key){
            parent::__construct([
                new Step(1, 'Thủ tục', sprintf('%squang-tri/tiep-nhan-online/chon-thu-tuc-nop-ho-so', SITE_ROOT)),
                new Step(2, 'Thông tin người nộp', sprintf('%squang-tri/tiep-nhan-online/nhap-thong-tin-nguoi-nop-ho-so?sid=%s', SITE_ROOT, $applier->getId())),
                new Step(3, 'Thông tin hồ sơ', sprintf('%squang-tri/tiep-nhan-online/nhap-thong-tin-ho-so?sid=%s', SITE_ROOT, $applier->getId())),
                new Step(4, 'Lệ phí hồ sơ', sprintf('%squang-tri/tiep-nhan-online/nhap-le-phi-ho-so?sid=%s', SITE_ROOT, $applier->getId())),
                new Step(5, 'Nộp hồ sơ', sprintf('%squang-tri/tiep-nhan-online/xac-nhan-thong-tin-nop?sid=%s', SITE_ROOT, $applier->getId()))
            ]);
        }
        else if (self::DICH_VU_CONG_BCA_2B == $key){
            parent::__construct([
                new Step(1, 'Thủ tục', sprintf('%sbo-cong-an/bo-thu-tuc?muc_do=MUC_DO_3,MUC_DO_4', SITE_ROOT)),
                new Step(2, 'Thông tin người nộp', sprintf('%sbo-cong-an/tiep-nhan-online/nhap-thong-tin-nguoi-nop-ho-so?sid=%s', SITE_ROOT, $applier->getId())),
                new Step(3, 'Nộp hồ sơ', sprintf('%sbo-cong-an/tiep-nhan-online/xac-nhan-thong-tin-nop?sid=%s', SITE_ROOT, $applier->getId()))
            ]);
        }
        else if (self::DICH_VU_CONG_BCA_3B == $key){
            parent::__construct([
                new Step(1, 'Thủ tục', sprintf('%sbo-cong-an/bo-thu-tuc?muc_do=MUC_DO_3,MUC_DO_4', SITE_ROOT)),
                new Step(2, 'Thông tin người nộp', sprintf('%sbo-cong-an/tiep-nhan-online/nhap-thong-tin-nguoi-nop-ho-so?sid=%s', SITE_ROOT, $applier->getId())),
                new Step(3, 'Thông tin hồ sơ', sprintf('%sbo-cong-an/tiep-nhan-online/nhap-thong-tin-ho-so?sid=%s', SITE_ROOT, $applier->getId())),
                new Step(4, 'Nộp hồ sơ', sprintf('%sbo-cong-an/tiep-nhan-online/xac-nhan-thong-tin-nop?sid=%s', SITE_ROOT, $applier->getId()))
            ]);
        }
        else if (self::DICH_VU_CONG_BCA_NEW == $key){
            parent::__construct([
                new Step(1, 'Thủ tục', sprintf('%sbo-cong-an/bo-thu-tuc?muc_do=MUC_DO_3,MUC_DO_4', SITE_ROOT)),
                new Step(2, 'Thông tin người nộp', sprintf('%sbo-cong-an/tiep-nhan-online/nhap-thong-tin-nguoi-nop-ho-so?sid=%s', SITE_ROOT, $applier->getId())),
                new Step(3, 'Thông tin hồ sơ', sprintf('%sbo-cong-an/tiep-nhan-online/nhap-thong-tin-ho-so?sid=%s', SITE_ROOT, $applier->getId())),
                new Step(4, 'Lệ phí hồ sơ', sprintf('%sbo-cong-an/tiep-nhan-online/nhap-le-phi-ho-so?sid=%s', SITE_ROOT, $applier->getId())),
                new Step(5, 'Nộp hồ sơ', sprintf('%sbo-cong-an/tiep-nhan-online/xac-nhan-thong-tin-nop-moi?sid=%s', SITE_ROOT, $applier->getId()))
            ]);
        }
        else if (self::DICH_VU_CONG_1B == $key){
            parent::__construct([
                new Step(1, 'Thủ tục', sprintf('%sdich-vu-cong/tiep-nhan-online/chon-thu-tuc-nop-ho-so', SITE_ROOT)),
                new Step(2, 'Thông tin người nộp', sprintf('%sdich-vu-cong/tiep-nhan-online/nhap-thong-tin-nguoi-nop-ho-so?sid=%s', SITE_ROOT, $applier->getId())),
                new Step(3, 'Nộp hồ sơ', sprintf('%sdich-vu-cong/tiep-nhan-online/xac-nhan-thong-tin-nop?sid=%s', SITE_ROOT, $applier->getId()))
            ]);
        }
        else{
            parent::__construct([
                new Step(1, 'Thủ tục', sprintf('%strang-chu/dich-vu-cong/chon-thu-tuc-nop-ho-so', SITE_ROOT)),
                new Step(2, 'Thông tin người nộp', sprintf('%strang-chu/dich-vu-cong/nhap-thong-tin-nguoi-nop-ho-so?sid=%s', SITE_ROOT, $applier->getId())),
                new Step(3, 'Thông tin hồ sơ', sprintf('%strang-chu/dich-vu-cong/nhap-thong-tin-ho-so?sid=%s', SITE_ROOT, $applier->getId())),
                new Step(4, 'Lệ phí hồ sơ', sprintf('%strang-chu/dich-vu-cong/nhap-le-phi-ho-so?sid=%s', SITE_ROOT, $applier->getId())),
                new Step(5, 'Nộp hồ sơ', sprintf('%strang-chu/dich-vu-cong/xac-nhan-thong-tin-nop?sid=%s', SITE_ROOT, $applier->getId()))
            ]);
        }
        $this->setApplier($applier);
        if (is_numeric($activeStepId)) {
            $this->getItem($activeStepId)->setActive(true);
        }
    }
    
    public function getApplier() {
        return $this->applier;
    }

    public function setApplier(Applier $applier) {
        $this->applier = $applier;
    }
    
    public function getActiveStep() {
        foreach ($this->getItems() as $item) {
            if ($item->getActive()) {
                return $item;
            }
        }
    }
    
    public function getPrevStep() {
        $step = $this->getActiveStep();
        $id = $step->getId();
        return $step && $id > 1 ? $this->getItem($id - 1) : null;
    }
    
    public function toHtml($isReview = false) {
        $items = $this->getItems();
        if ($activeStep = $this->getActiveStep()) {
            $activeStepId = $activeStep->getId();
            
            $items[$activeStepId-1]->setLink(null);
            if ($activeStepId === 1) {
                $items[0]->setLink(null);
                if (!$this->getApplier()->applied()) {
                    for($i=$activeStepId;$i<count($items);$i++){
                        $items[$i]->setLink(null);
                    }
                }
            } else{
                if ($this->getApplier()->applied()) {
                    $items[0]->setLink(null);
                } else {
                    $start = $activeStepId;
                    if($isReview){
                        $start = 1;
                    }
                    for($i=$start;$i<count($items);$i++){
                        $items[$i]->setLink(null);
                    }
                }
            }
        }
        return $this->toNode()->toString();
    }
    
    public function toNode() {
        $ul = new Node('ul', null, ['class' => 'gsi-step-indicator triangle gsi-style-1 gsi-transition']);
        foreach ($this->getItems() as $item) {
            $ul->appendChild($item->toNode());
        }
        return $ul;
    }

    public function sort(array $options = array()) {
        
    }

}
