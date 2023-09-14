<?php

class VoucherForm extends InputForm {
    public function __construct()
    {
        parent::__construct();
        $field = DataInputFactory::Create(DataInputFactory::TEXT, "rcpt_name", "Име на получателя", 1);
        $this->addInput($field);

//        $field = DataInputFactory::Create(DataInputFactory::TEXT, "rcpt_email", "E-Mail на получателя", 1);
//        $field->setValidator(new EmailValidator());
//        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::TEXT, "name", "Вашето име", 1);
        $this->addInput($field);

//        $field = DataInputFactory::Create(DataInputFactory::TEXT, "email", "Вашият E-Mail", 1);
//        $field->setValidator(new EmailValidator());
//        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::TEXT, "phone", "Вашият телефон", 1);
        $this->addInput($field);

//        $aw1 = new ArrayDataIterator(array("Рожден ден", "Коледа", "Основен"));
//        $field = DataInputFactory::Create(DataInputFactory::RADIO, "reason", "Сертификатът е за", 1);
//        $rend = $field->getRenderer();
//        $rend->setIterator($aw1);
//        $rend->getItemRenderer()->setValueKey(ArrayDataIterator::KEY_VALUE);
//        $rend->getItemRenderer()->setLabelKey(ArrayDataIterator::KEY_VALUE);
//        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::TEXT, "amount", "Сума", 1);
        $field->setValidator(new NumericValidator());
        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::TEXTAREA, "note", "Забележка (опционално)", 0);
        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::CHECKBOX, "agree_terms", "Ваучерите за подарък немогат да бъда връщани", 1);
        $field->setValidator(new EmptyValueValidator());
        $this->addInput($field);

    }
}
