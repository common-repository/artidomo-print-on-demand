(function ($) {
  "use strict";
  const ajaxurl = adminajax.ajaxurl;
  var defname, defsku;
  var defname = $(".shop-product h2").html();
  var defsku = $(".shop-product h4").html();
  const artidomomainproduct = $("#artidomo_main_product").html();
  /**
   * All of the code for your admin-facing JavaScript source
   * should reside in this file.
   *
   * Note: It has been assumed you will write jQuery code here, so the
   * $ function reference has been prepared for usage within the scope
   * of this function.
   *
   * This enables you to define handlers, for when the DOM is ready:
   *
   * $(function() {
   *
   * });
   *
   * When the window is loaded:
   *
   * $( window ).load(function() {
   *
   * });
   *
   * ...and/or other possibilities.
   *
   * Ideally, it is not considered best practise to attach more than a
   * single DOM-ready or window-load handler for a particular page.
   * Although scripts in the WordPress core, Plugins and Themes may be
   * practising this, we should strive to set a better example in our own work.
   */
  $("#artidomo_main_product").select2();
  $("#artidomo_product_cat").change(function (e) {
    $(".shop-product").html("");
    e.preventDefault();
    var value = $(this).val();
    $.ajax({
      type: "POST",
      url: ajaxurl,
      data: { action: "artidomo_get_product", product_cat: value },
      beforeSend: function () {
        $(".product-filter-loader").removeClass("dont-show");
      },
      success: function (response) {
        if (response.success) {
          $("#artidomo_product").html(response.data.html);
          $("#edit_id").val(response.data.product_id);
        } else {
          alert("error");
        }
        $(".product-filter-loader").addClass("dont-show");
      },
    });
  });
  $("#artidomo_main_variant").change(function (e) {
    if ($("#artidomo_product").val().length > 0) $("#ac_add_variation").show();
    else $("#ac_add_variation").hide();
    let price = $("#artidomo_main_variant option:selected").attr("price");
    if (price)
      $(".artidomo-product-price").html("<strong>Price: </strong>" + price);
    else $(".artidomo-product-price").html("");
  });
  $("#artidomo_variations").change(function (e) {
    let thiselected = $("#artidomo_variations option:selected");
    let price = thiselected.attr("price");
    let sku = thiselected.attr("sku");
    let name = thiselected.attr("name");
    $(".product-meta-sec h2, .shop-product h2").html(name + " - " + price);
    $(".product-meta-sec h4, .shop-product h4").html("SKU - " + sku);

    if (thiselected.val().length == 0) {
      $(".product-meta-sec h2, .shop-product h2").html(defname);
      $(".product-meta-sec h4, .shop-product h4").html(defsku);
    }
  });
  $("#artidomo_product").change(function (e) {
    e.preventDefault();
    var value = $(this).val();
    $("#artidomo_variations").removeAttr("disabled");
    if ($("#artidomo_main_product").val()) $("#ac_add_variation").show();
    else $("#ac_add_variation").hide();
    $.ajax({
      type: "POST",
      url: ajaxurl,
      data: { action: "artidomo_get_variation", product_id: value },
      success: function (response) {
        if (response.success) {
          $(".shop-product").html(response.data.product_html);
          if (response.data.is_variable) {
            $("#artidomo_variations").html(response.data.html);
            if ($("#artidomo_main_product").val())
              $("#ac_add_variation").show();
            else $("#ac_add_variation").hide();
            defname = $(".product-meta-sec h2").html();
            defsku = $(".product-meta-sec h4").html();
          } else {
            $("#artidomo_variations").attr("disabled", "disabled");
          }
        } else {
          alert("error");
        }
      },
    });
  });

  $("#artidomo_file_handling").change(function (e) {
    e.preventDefault();
    var value = $(this).val();
    $("#static_file_method,.ac-file-info").hide();
    $("#image_id").hide();
    if (value == "static") {
      $("#static_file_method,.ac-file-info").show();
      $("#image_id").show();
    }
  });

  $("#artidomo_main_product").change(function (e) {
    e.preventDefault();
    var value_id = $(this).val();
    if (!value_id) {
      return false;
    }
    $.ajax({
      type: "POST",
      url: ajaxurl,
      data: { action: "artidomo_get_main_product_variation", id: value_id },
      dataType: "json",
      beforeSend: function () {
        $(".product-filter-loader").removeClass("dont-show");
      },
      success: function (response) {
        if (response.res == "success") {
          $("#artidomo_main_variant").html(response.html);
          let price = $("#artidomo_main_product option:selected").attr("price");
          if (price)
            $(".artidomo-no-var-product-price").html(
              "<strong>Price: </strong>" + price
            );
          else $(".artidomo-no-var-product-price").html();
        } else {
          alert(response.msg);
        }
        if ($("#artidomo_product").val().length > 0)
          $("#ac_add_variation").show();
        else $("#ac_add_variation").hide();
        $(".product-filter-loader").addClass("dont-show");
      },
    });
  });
  $("#artidomo_main_product_large_length").change(function (e) {
    e.preventDefault();
    if ($("#artidomo_main_product_category").hasClass("first-timer")) {
      $("#artidomo_main_product_category").removeClass("first-timer");
    }
  });

  $("input[name=artiproductsize]").change(function () {
    if ($(this).val() == "no") {
      let artidomocat = $(
        "#artidomo_main_product_category option:selected"
      ).val();
      let value_id = $("#artidomo_main_product_category option:selected").val();
      $(".artidomo-main-length-div").hide();
      $(
        "#artidomo_main_product_small_length, #artidomo_main_product_large_length"
      )
        .find("option:first-child")
        .prop("selected", true)
        .trigger("change");
      if (artidomocat != "") {
        $.ajax({
          type: "POST",
          url: ajaxurl,
          data: {
            action: "artidomo_get_main_product_products",
            id: value_id,
            checksize: "no",
          },
          dataType: "json",
          beforeSend: function () {
            $(".product-filter-loader").removeClass("dont-show");
            $("#artidomo_main_product").html("");
          },
          success: function (response) {
            if (response.res == "success") {
              $("#artidomo_main_product").html(response.html);
            } else {
              alert(response.msg);
            }
            $(".product-filter-loader").addClass("dont-show");
          },
        });
      }
    } else {
      $(".artidomo-main-length-div").show();
      $("#artidomo_main_product").html(artidomomainproduct);
    }
  });
  $("#artidomo_main_product_category").change(function (e) {
    e.preventDefault();
    $("input:radio[name=artiproductsize]")
      .filter("[value=yes]")
      .prop("checked", true);
    $("#artidomo_main_product").html(artidomomainproduct);
    $(".artidomo-no-var-product-price").html("");
  });
  $(
    "#artidomo_main_product_category, #artidomo_main_product_small_length, #artidomo_main_product_large_length"
  ).change(function (e) {
    e.preventDefault();
    let psize = $("input[name=artiproductsize]:checked").val();
    if (psize != "yes") {
      return false;
    }
    $(".artidomo-product-price").html("");
    $(".artidomo-no-var-product-price").html("");
    var value_id = $("#artidomo_main_product_category option:selected").val();
    var smlength = $(
      "#artidomo_main_product_small_length option:selected"
    ).val();
    var lglength = $(
      "#artidomo_main_product_large_length option:selected"
    ).val();
    if ($("#artidomo_main_product_category").hasClass("first-timer")) {
      return false;
    } else {
      if (
        value_id.length === 0 ||
        smlength.length === 0 ||
        lglength.length == 0
      ) {
        return false;
      }
    }
    $.ajax({
      type: "POST",
      url: ajaxurl,
      data: {
        action: "artidomo_get_main_product_products",
        id: value_id,
        smlength,
        lglength,
      },
      dataType: "json",
      beforeSend: function () {
        $(".product-filter-loader").removeClass("dont-show");
        $("#artidomo_main_product").html("");
      },
      success: function (response) {
        if (response.res == "success") {
          $("#artidomo_main_product").html(response.html);
        } else {
          alert(response.msg);
        }
        $(".product-filter-loader").addClass("dont-show");
      },
    });
  });

  $(document).on("click", "#ac_add_variation", function () {
    let our_variant = $("#artidomo_variations").val();
    let our_variant_name = $("#artidomo_variations").find(":selected").text();
    let our_product = $("#artidomo_product").val();
    let our_product_name = $("#artidomo_product").find(":selected").text();
    let arti_variant = $("#artidomo_main_variant").val();
    let artimain_variant = $("#artidomo_main_product").val();
    let artimain_variant_name = $("#artidomo_main_product")
      .find(":selected")
      .text();
    let artimain_variant_size = $("#artidomo_main_product")
      .find(":selected")
      .attr("size");
    let variant = "";
    if (our_variant != "" && arti_variant != "") {
      variant =
        '<li><span class="ac-p-var"><label>Child Variation</label>' +
        our_product_name +
        " - " +
        our_variant_name +
        '</span><span class="ac-a-var"><label>Main Variation</label>' +
        artimain_variant_name +
        " - " +
        arti_variant +
        "-" +
        artimain_variant_size +
        '</span><span class="var-remove">-</span><input type="hidden" class="ac_var_id" name="ac_var_id[]" value="' +
        our_variant +
        '"><input  class="ar_pr_id" type="hidden" name="ar_pr_id[]" value="' +
        artimain_variant +
        '"><input  class="ar_var_id" type="hidden" name="ar_var_id[]" value="' +
        arti_variant +
        '"><input class="ar_var_name" type="hidden" name="ar_var_name[]" value="' +
        artimain_variant_name +
        '"><input class="ar_var_size" type="hidden" name="ar_var_size[]" value="' +
        artimain_variant_size +
        '"></li>';
    } else if (our_variant != "") {
      variant =
        '<li><span class="ac-p-var"><label>Child Variation</label>' +
        our_product_name +
        " - " +
        our_variant_name +
        '</span><span class="ac-a-var"><label>Main Variation</label>' +
        artimain_variant_name +
        "-" +
        artimain_variant_size +
        '</span><span class="var-remove">-</span><input type="hidden" class="ac_var_id" name="ac_var_id[]" value="' +
        our_variant +
        '"><input  class="ar_pr_id" type="hidden" name="ar_pr_id[]" value="' +
        artimain_variant +
        '"><input class="ar_var_name" type="hidden" name="ar_var_name[]" value="' +
        artimain_variant_name +
        '"><input class="ar_var_size" type="hidden" name="ar_var_size[]" value="' +
        artimain_variant_size +
        '"></li>';
    } else {
      variant =
        '<li><span class="ac-p-var"><label>Child Variation</label>' +
        our_product_name +
        '</span><span class="ac-a-var"><label>Main Variation</label>' +
        arti_variant +
        "-" +
        artimain_variant_size +
        '</span><span class="var-remove">-</span><input type="hidden" class="ac_var_id" name="ac_var_id[]" value="' +
        our_product +
        '"><input  class="ar_pr_id" type="hidden" name="ar_pr_id[]" value="' +
        arti_variant +
        '"><input class="ar_var_size" type="hidden" name="ar_var_size[]" value="' +
        artimain_variant_size +
        '"></li>';
    }
    if (variant) {
      $(".ac-added-variation-sec").show();
      $(".ac-added-variation").append(variant);
    }
  });

  $(document).on("click", ".var-remove", function () {
    $(this).parent().remove();
  });
})(jQuery);
