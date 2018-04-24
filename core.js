(function(window, $, undefined) {

  $(document).ready(function() {
    if ($().select2) {
      var wc_address_book_select_select2 = function() {
        $('select#shipping_address:visible, select#address_book:visible').each(function() {
          $(this).select2();
        });
      };

      wc_address_book_select_select2();
    }

    function shipping_checkout_field_prepop() {

      var that = $('#addressbook_field #addressbook');
      var name = $(that).val();

      if (name !== undefined) {
        if ('add_new' == name) {

          // Clear values when adding a new address.
          $('.shipping_address input').not($('#shipping_country')).each(function() {
            $(this).val('');
          });

          // Set Country Dropdown.
          // Don't reset the value if only one country is available to choose.
          if (typeof $('#shipping_country').attr('readonly') == 'undefined') {
            $('#shipping_country').val('').change();
            $("#shipping_country_chosen").find('span').html('');
          }

          // Set state dropdown.
          $('#shipping_state').val('').change();
          $("#shipping_state_chosen").find('span').html('');

          return;
        }

        if (name.length > 0) {

          $(that).closest('.shipping_address').addClass('blockUI blockOverlay wc-updating');

          $.ajax({
            url: woo_address_book.ajax_url,
            type: 'post',
            data: {
              action: 'functions',
              name: name
            },
            dataType: 'json',
            success: function(response) {

              // Loop through all fields incase there are custom ones.

              // Set First Name
              $('#shipping_first_name').val(response.shipping_first_name).change();
              $("#shipping_first_name_chosen").find('span').html(response.shipping_first_name_text);

              // Set First Name
              $('#shipping_last_name').val(response.shipping_last_name).change();
              $("#shipping_lastt_name_chosen").find('span').html(response.shipping_last_name_text);

              Object.keys(response).forEach(function(key) {
                $('#shipping_address_1').val(response.shipping_address_1);
                $('#' + key).val(response[key]);
              });

              // Set Country Dropdown.
              $('#shipping_country').val(response.shipping_country).change();
              $("#shipping_country_chosen").find('span').html(response.shipping_country_text);

              // Set state dropdown.
              $('#shipping_state').val(response.shipping_state);
              var stateName = $('#shipping_state option[value="' + response.shipping_state + '"]').text();
              $("#s2id_shipping_state").find('.select2-chosen').html(stateName).parent().removeClass('select2-default');

              // Remove loading screen.
              $('.shipping_address').removeClass('blockUI blockOverlay wc-updating');

            }
          });

        }
      }
    }
    shipping_checkout_field_prepop();

    $('#addressbook_field #addressbook').change(function() {
      shipping_checkout_field_prepop();
    });
  });

})(window, jQuery);
