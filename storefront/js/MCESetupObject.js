function getMCESetupObject() {

    let mce_setup_object = {
        schema: 'html5',

        extended_valid_elements: 'img[*],a[*]',

        // Location of TinyMCE script
        script_url: SPARK_LOCAL + '/js/tiny_mce/tinymce.min.js',

        strict_loading_mode: true,
        theme: "silver",

        //
        entity_encoding: "raw",
        force_p_newlines: true,
        force_br_newlines: true,

        ///ver 4
        menubar: false,
        toolbar1: 'code | undo redo | fontselect fontsizeselect | bold italic underline strikethrough | alignleft aligncenter alignright | bullist numlist outdent indent blockquote',
        toolbar2: 'link unlink anchor | image media code | insertdatetime preview | forecolor backcolor | charmap | spark_imagebrowser |',
        plugins: 'code link image lists charmap anchor insertdatetime media paste code',

        // invalid_elements: 'iframe,object,embed',

        resize: 'both',

        branding: false,

        verify_html: 1,
        media_restrict: false,

        width: '100%',
        height: '300px',

        //content_css: "/mycontent.css",
        content_style: "p { margin: 0; } body { line-height: 1; }",
    };
    return mce_setup_object;
}
