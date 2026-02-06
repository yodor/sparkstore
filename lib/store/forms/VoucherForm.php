<?php

class VoucherForm extends InputForm {
    public function __construct()
    {
        parent::__construct();
        $field = DataInputFactory::Create(InputType::TEXT, "rcpt_name", "Име на получателя", 1);
        $this->addInput($field);

//        $field = DataInputFactory::Create(InputType::TEXT, "rcpt_email", "E-Mail на получателя", 1);
//        $field->setValidator(new EmailValidator());
//        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::TEXT, "name", "Вашето име", 1);
        $this->addInput($field);

//        $field = DataInputFactory::Create(InputType::TEXT, "email", "Вашият E-Mail", 1);
//        $field->setValidator(new EmailValidator());
//        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::TEXT, "phone", "Вашият телефон", 1);
        $this->addInput($field);

//        $aw1 = new ArrayDataIterator(array("Рожден ден", "Коледа", "Основен"));
//        $field = DataInputFactory::Create(InputType::RADIO, "reason", "Сертификатът е за", 1);
//        $rend = $field->getRenderer();
//        $rend->setIterator($aw1);
//        $rend->getItemRenderer()->setValueKey(ArrayDataIterator::KEY_VALUE);
//        $rend->getItemRenderer()->setLabelKey(ArrayDataIterator::KEY_VALUE);
//        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::TEXT, "amount", "Сума", 1);
        $field->setValidator(new NumericValidator());
        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::TEXTAREA, "note", "Забележка (опционално)", 0);
        $this->addInput($field);

        $field = DataInputFactory::Create(InputType::CHECKBOX, "agree_terms", "Ваучерите за подарък немогат да бъда връщани", 1);
        $field->setValidator(new EmptyValueValidator());
        $this->addInput($field);

    }
}
