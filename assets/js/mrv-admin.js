jQuery(document).ready(function ($) {
    $('#ui-id-1').ready(function () {
      //  $(document).find('#ui-id-1').trigger('click');

        $('#mrv-copy-shortcode').on('click', function (et) {
            et.preventDefault();
            let el_text = $('#mrv_post_settinga_shortcode_bar').find('input[type=text]');
            el_text.select();
            //    el_text.setSelectionRange(0, 99999); // for mobile devices
            document.execCommand("copy");
        })
    });

    
    // $('#save_style').click(function() {
    //     var selectedStyle = $('#mrv_styles').val();
    //     var data = {
    //         'action': 'save_style',
    //         'style': selectedStyle
    //     };
    //     $.post(ajaxurl, data, function(response) {
    //         alert('Style saved successfully.');
    //     });
    // });


    function mrvcsf_chnage_title() {
        let data = document.querySelectorAll('.csf-cloneable-item');

        $.each(data, function (index, value) {

            let replaced_text = "";
            let csf_clone_ityem = $(this).find('.csf-cloneable-value')

            replaced_text = $(this).find('.mrv_custom_item input').val()
            $(csf_clone_ityem).html(replaced_text);

        });
    }

    mrvcsf_chnage_title();




})




