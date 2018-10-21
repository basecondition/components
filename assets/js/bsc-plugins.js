/**
 * @package basecondition/components
 * @author Joachim Doerr
 * @copyright (C) mail@doerr-softwaredevelopment.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$(function () {
    tools_init();
    $(document).on('pjax:end', function() {
        tools_init();
    });
});

function tools_init() {
    var multi_select = $('.basecondition-multi-select'),
        multi_open_select = $('.basecondition-multi-select-open');
    if (multi_select.length) {
        multi_select.each(function () {
            $(this).baseconditionMultiselect({
                includeSelectAllOption: true,
                selectAllText: 'Select all',
                filterBehavior: 'both',
                enableFiltering: true,
                maxHeight: 350
            });
        });
    }
    if (multi_open_select.length) {
        multi_open_select.each(function () {
            $(this).baseconditionMultiselect({
                includeSelectAllOption: true,
                selectAllText: 'Select all',
                filterBehavior: 'both',
                enableFiltering: true,
                templates: {
                    button: '',
                    ul: '<ul class="bsc-multiselect-container checkbox-list"></ul>',
                    filter: '<li class="bsc-multiselect-item filter"><div class="input-group"><span class="input-group-addon"><i class="glyphicon glyphicon-search"></i></span><input class="form-control bsc-multiselect-search" type="text"></div></li>',
                    filterClearBtn: '<span class="input-group-btn"><button class="btn btn-default bsc-multiselect-clear-filter" type="button"><i class="glyphicon glyphicon-remove-circle"></i></button></span>',
                    li: '<li><a href="javascript:void(0);"><label></label></a></li>',
                    divider: '<li class="bsc-multiselect-item divider"></li>',
                    liGroup: '<li class="bsc-multiselect-item group"><label class="bsc-multiselect-group"></label></li>'
                }
            });
        });
    }

    store_mblock_init();
}

function checkboxpicker_init() {
    var checkbox = $('.basecondition-toggle');
    if (checkbox.length) {
        checkbox.each(function () {
            $(this).baseconditionToggle();
            var val = 0;
            if ($(this).prop('checked')) {
                val = 1;
            }

            $(this).parent().parent().append('<input type="hidden" name="' + $(this).attr('name').replace('[1]', '') + '" value="' + val + '"/>');
            $(this).attr('name', '');

            $(this).change(function() {
                var val = 0;
                if ($(this).prop('checked')) {
                    val = 1;
                }
                $(this).parent().parent().find('input').val(val);
            })
        });
    }
}

function store_mblock_init() {
    var af_dropdown = $('.base_mblock .dropdown');
    if (af_dropdown.length) {
        af_dropdown.each(function() {
            var target = $(this).parent().next();
            $(this).find('a').each(function(){
                // ajax
                $(this).unbind().bind('click', function(event){
                    var _li = $(this).parent(),
                        _a = $(this);

                    if (!_li.hasClass('disabled')) {

                        $.ajax({
                            url: $(this).attr('data-link'),
                            success: function (result) {
                                target.append(result);
                                mblock_init();

                                _li.addClass('disabled');
                                _a.find('span').remove();
                                _a.append(' <span class="glyphicon glyphicon-ok">');
                            }
                        });

                    }
                    event.toElement.parentElement.click();
                    return false;
                })
            });
        });
        store_mblock_callback_run(af_dropdown);
    }
}

function store_mblock_callback_run(af_dropdown) {
    mblock_module.registerCallback('reindex_end', function (item) {
        af_dropdown.each(function () {
            var _drop = $(this),
                target = $(this).parent().next();
            target.find('.mblock_wrapper').each(function () {
                if ($(this).find('> div').length == 0) {
                    var type_key = $(this).data('type_key');
                    _drop.find('li').each(function () {
                        if ($(this).data('type_key') == type_key) {
                            $(this).removeClass('disabled');
                            $(this).find('a span').remove();
                        }
                    });
                }
            });
        });
    });
}