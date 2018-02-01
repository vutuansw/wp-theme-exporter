jQuery(function ($) {
    'use strict';

    $.fn.serializeObj = function () {
        var arr = $(this).serializeArray();
        console.log(arr);
        var obj = {};
        $.each(arr, function (key, item) {
            if (obj.hasOwnProperty(item.name)) {
                if (typeof obj[item.name] != 'object') {
                    obj[item.name] = [obj[item.name]];

                }
                obj[item.name].push(item.value);

            } else {
                obj[item.name] = item.value;
            }

        });
        return obj;
    }
    
    var $document = $(document);
    var $form = $('.wp_theme_exporter_form');

    /**
     * Field select multiple
     */
    if ($('.wdwb-select-multiple').length) {

        $('.wdwb-select-multiple').selectize({
            plugins: ['remove_button']
        });

    }

    /**
     * Group Tabs
     */
    $document.on('click', '.wdwb_group .group_nav a', function (e) {

        var $this = $(this);
        var id = $this.attr('href');

        $this.closest('ul').find('.active').removeClass('active');
        $this.addClass('active');

        $('.wdwb_group .group_item.active').removeClass('active');

        var $panel = $('.wdwb_group ' + id);
        $panel.addClass('active');
        if (id === '#wp_theme_exporter-group_2') {

            $('.wp_theme_exporter_footer #export').hide();
            $('.wp_theme_exporter_footer #restore_default,.wp_theme_exporter_footer #save_change').show();
        } else {
            $('.wp_theme_exporter_footer #export').show();
            $('.wp_theme_exporter_footer #restore_default,.wp_theme_exporter_footer #save_change').hide();
        }

        $document.trigger('wdwb_group_active', [$panel]);

        e.preventDefault();
    });

    /**
     * Select theme on changed
     */
    $form.on('change', '#export_theme', function (e) {

        var $this = $(this);

        var $div = $this.closest('div');
        $div.find('.alert').remove();

        if ($this.val() != '' && $.trim($('#export_plugin').val()) != '') {

            var plugins = $('#export_plugin').val();

            plugins = plugins.join('.zip</code>,<code> ');

            var pluginFolder = $('#theme_plugin_folder').val();
            if (pluginFolder == '') {
                pluginFolder = 'plugins';
            }

            var text = '<code>' + plugins + '.zip</code> ' + wp_theme_exporter.form.zipinfo + ' <code>' + $this.val() + '/' + pluginFolder + '/</code>';


            $div.append('<div class="alert alert-warning">' + text + '</div>');
        }
    });

    /**
     * Select plugin on changed
     */
    $form.on('change', '#export_plugin', function () {
        $form.find('#export_theme').trigger('change');
    });
    
    /**
     * Export
     */
    $form.on('click', '#export', function (e) {

        var $this = $(this);

        if ($this.attr('disabled') === 'disabled') {
            return false;
        }

        var data = $form.serializeObj();
        data.action = 'wp_theme_exporter_export';
        
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: data,
            beforeSend: function () {
                $('.wp_theme_exporter_footer .warning').empty();
                $this.attr('disabled', 'disabled').before('<span class="spinner is-active"></span>');
                $this.find('span').text(wp_theme_exporter.form.plswait);

            },
            success: function (res) {

                if (res.success) {
                    $('.wp_theme_exporter_modal__body').html(res.data);
                    $('.wp_theme_exporter_modal').fadeIn();
                } else {
                    $('.wp_theme_exporter_footer .warning').html(res.data);
                }

                $this.prev('.spinner').remove();
                $this.removeAttr('disabled').find('span').text(wp_theme_exporter.form.export);
            }
        });

        e.preventDefault();
    });

    /**
     * Save change settings
     */
    $form.on('click', '#save_change', function (e) {
        e.preventDefault();

        var $this = $(this);

        if ($this.attr('disabled') === 'disabled') {
            return false;
        }

        var data = $form.serializeObj();
        data.action = 'wp_theme_exporter_save_settings';

        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: data,
            beforeSend: function () {

                $this.attr('disabled', 'disabled').before('<span class="spinner is-active"></span>');
                $this.find('span').text(wp_theme_exporter.form.plswait);

            },
            success: function (res) {
                $this.prev('.spinner').remove();
                $this.removeAttr('disabled').find('span').text(wp_theme_exporter.form.savechange);
            }
        });

    });

    /**
     * Reset settings to default
     */
    $form.on('click', '#restore_default', function (e) {
        e.preventDefault();

        var $this = $(this);

        if ($this.attr('disabled') === 'disabled') {
            return false;
        }

        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {action: 'wp_theme_exporter_reset_settings', wp_theme_exporter_nonce: $('[name="wp_theme_exporter_nonce"]').val()},
            beforeSend: function () {

                $this.attr('disabled', 'disabled').before('<span class="spinner is-active"></span>');
                $this.find('span').text(wp_theme_exporter.form.plswait);

            },
            success: function (res) {
                $this.prev('.spinner').remove();
                $this.removeAttr('disabled').find('span').text(wp_theme_exporter.form.reset);
            }
        });
    });

    /**
     * Close modal
     */
    $document.on('click', '.wp_theme_exporter_modal .modal_close', function (e) {
        $(this).closest('.wp_theme_exporter_modal').fadeOut();
        e.preventDefault();
    });

    /**
     * Active status when click download file
     */
    $document.on('click', '.wp_theme_exporter_modal__body li a', function (e) {
        $(this).addClass('active');
    });
});