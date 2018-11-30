/**
 * @package basecondition/components
 * @author Joachim Doerr
 * @copyright (C) mail@doerr-softwaredevelopment.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

let bsc_checkbox = '.basecondition-toggle',
    bsc_multi_select = '.basecondition-multi-select',
    bsc_multi_open_select = '.basecondition-multi-select-open',
    bsc_dropdown = '.base_mblock .dropdown',
    bsc_currency_input = 'input[data-bsc-currency="1"]',
    form_element = '.base-form';

// for older mblock versions
$(document).on('ready', function () {
    if (typeof mblock_module === 'object') {
        mblock_module.registerCallback('reindex_end', function () {
            if (mblock_module.lastAction === 'add_item') {
                tools_destroy(mblock_module.affectedItem);
                tools_init(mblock_module.affectedItem);
            }
        });
    }
});

$(document).on('rex:ready', function () {
    tools_init($(form_element));
});

function tools_init($element) {
    mblock_dropdown_init($element);
    checkboxpicker_init($element);
    multiselects_init($element);
    currency_input_init($element);
}

function tools_destroy($element) {
    checkboxpicker_destroy($element);
    multiselects_destroy($element);
    currency_input_destroy($element);
}

function currency_input_init($element) {
    if ($element.find(bsc_currency_input).length) {
        $element.find(bsc_currency_input).each(function (index) {
            let $currencySymbol = '€';
            if ($(this).attr('data-currency-symbol')) {
            }
            $(this).parent().prepend('<div class="input-group"><span class="input-group-addon">' + $currencySymbol + '</span></div>');
            let $group = $(this).parent().find('.input-group'),
                $uid = Math.random().toString(16).slice(2) + '_' + index;
            $group.append($(this));
            $group.find('input').addClass('bscautonumeric_' + $uid);
            new BscAutoNumeric('.bscautonumeric_' + $uid, {
                digitGroupSeparator        : BscAutoNumeric.options.digitGroupSeparator.noSeparator,
                decimalCharacter           : ',',
                decimalCharacterAlternative: '.',
            });
        });
    }
}

function currency_input_destroy($element) {
    if ($element.find(bsc_currency_input).length) {
        $element.find(bsc_currency_input).each(function () {
            let parent_element = $(this).parent(),
                $dd = $(this).parents('dd'),
                $classes = $(this).attr("class").split(" "),
                $myclass;

            $(this).appendTo($dd);
            parent_element.remove();

            for (var i = 0, max = $classes.length; i < max; i++) {
                let $class = $classes[i].split("_");
                if ($class[0] === "bscautonumeric") {
                    $myclass = $classes[i];
                    let $val = $(this).val().replace(',', '.');
                    $(this).off().removeClass($myclass);
                    $(this).replaceWith($(this).clone().val($val));
                    break;
                }
            }
        });
    }
}

function multiselects_init($element) {
    if ($element.find(bsc_multi_select).length) {
        $element.find(bsc_multi_select).each(function () {
            var maxheight = 350,
                numberdisplay = 3,
                search = 1,
                selectall = 1;
            if (typeof $(this).data('max-height') !== 'undefined') {
                maxheight = $(this).data('max-height');
            }
            if (typeof $(this).data('number-display') !== 'undefined') {
                numberdisplay = $(this).data('number-display');
            }
            if (typeof $(this).data('search') !== 'undefined') {
                search = $(this).data('search');
            }
            if (typeof $(this).data('select-all') !== 'undefined') {
                selectall = $(this).data('select-all');
            }
            $(this).baseconditionMultiselect({
                includeSelectAllOption: selectall,
                selectAllText: 'Select all',
                filterBehavior: 'both',
                enableFiltering: search,
                maxHeight: maxheight,
                numberDisplayed: numberdisplay,
            });
        });
    }
    if ($element.find(bsc_multi_open_select).length) {
        $element.find(bsc_multi_open_select).each(function () {
            var maxheight = false,
                search = 1,
                selectall = 1;
            if (typeof $(this).data('max-height') !== 'undefined') {
                maxheight = $(this).data('max-height');
            }
            if (typeof $(this).data('search') !== 'undefined') {
                search = $(this).data('search');
            }
            if (typeof $(this).data('select-all') !== 'undefined') {
                selectall = $(this).data('select-all');
            }
            $(this).baseconditionMultiselect({
                includeSelectAllOption: selectall,
                selectAllText: 'Select all',
                filterBehavior: 'both',
                enableFiltering: search,
                maxHeight: maxheight,
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
}

function multiselects_destroy($element) {
    multiselect_destroy($element, bsc_multi_select);
    multiselect_destroy($element, bsc_multi_open_select);
}

function multiselect_destroy($element, $select) {
    if ($element.find($select).length) {
        $element.find($select).each(function () {
            var parent_element = $(this).parent();
            $(this).appendTo($(this).parents('dd'));
            parent_element.remove();
        });
    }
}

function checkboxpicker_init($element) {
    if ($element.find(bsc_checkbox).length) {
        $element.find(bsc_checkbox).each(function () {
            $(this).baseconditionToggle();
            var val = 0;
            if ($(this).prop('checked')) {
                val = 1;
            }

            $(this).parent().parent().append('<input type="hidden" name="' + $(this).attr('name').replace('[1]', '') + '" value="' + val + '"/>');
            $(this).attr('name', '');

            $(this).change(function () {
                var val = 0;
                if ($(this).prop('checked')) {
                    val = 1;
                }
                $(this).parent().parent().find('input').val(val);
            })
        });
    }
}

function checkboxpicker_destroy($element) {
    if ($element.find(bsc_dropdown).length) {
        $element.find(bsc_dropdown).each(function () {

        });
    }
}

function mblock_dropdown_init($element) {
    if ($element.find(bsc_dropdown).length) {
        $element.find(bsc_dropdown).each(function () {
            var target = $(this).parent().next();
            $(this).find('a').each(function () {
                // ajax
                $(this).unbind().bind('click', function (event) {
                    var _li = $(this).parent(),
                        _a = $(this);

                    if (!_li.hasClass('disabled')) {

                        $.ajax({
                            url: $(this).attr('data-link'),
                            success: function (result) {
                                target.append(result);
                                mblock_init();
                                tools_init(target.find('.mblock_wrapper'));

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
        mblock_dropdown_callback_run($element.find(bsc_dropdown));
    }
}

function mblock_dropdown_callback_run($element) {
    mblock_module.registerCallback('reindex_end', function (item) {
        $element.each(function () {
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