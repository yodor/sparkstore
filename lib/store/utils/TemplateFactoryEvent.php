<?php
include_once("objects/SparkEvent.php");

class TemplateFactoryEvent extends SparkEvent
{
    const string TEMPLATE_CREATED = "template_created";
    const string TEMPLATE_RENDERED = "template_rendered";
}

?>