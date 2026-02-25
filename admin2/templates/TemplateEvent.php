<?php
class TemplateEvent extends SparkEvent
{
    const string CONFIG_CREATED = "CONFIG_CREATED";
    const string CONTENT_CREATED = "CONTENT_CREATED";

    const string CONTENT_INITIALIZED = "CONTENT_INITIALIZED";

    const string CONTENT_INSERTED = "CONTENT_INSERTED";

    const string CONTENT_INPUT_PROCESSED = "CONTENT_INPUT_PROCESSED";
    const string MENU_CREATED = "MENU_CREATED";
}