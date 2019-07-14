define(['jquery', 'uiComponent', 'mage/storage', 'Magento_Customer/js/customer-data', 'Magento_Checkout/js/action/get-totals', 'Magento_Checkout/js/model/totals', 'Magento_Checkout/js/model/quote', 'Netbaseteam_Opc/js/action/reload-shipping-method', 'Magento_Checkout/js/action/get-payment-information', /*'Netbaseteam_Opc/js/model/gift-wrap',*/ 'Magento_Ui/js/modal/confirm', 'Magento_Ui/js/modal/alert', 'mage/translate', 'Magento_Catalog/js/price-utils'], function ($, Component, storage, customerData, getTotalsAction, totals, quote, reloadShippingMethod, getPaymentInformation, /*giftWrapModel,*/ confirm, alertPopup, Translate, priceUtils) {
    "use strict";
    return Component.extend({
        params: '',
        defaults: {
            displayArea: 'before_details',
            template: 'Netbaseteam_Opc/summary/item/details/delete'
        },
        deleteItem: function (data) {
            var self = this;
            confirm({
                content: Translate('Do you want to remove the item from cart?'), actions: {
                    confirm: function () {
                        self.updateQty(data.item_id, 'delete', '');
                    }, always: function (event) {
                        event.stopImmediatePropagation();
                    }
                }
            });
        },
        showOverlay: function () {
            $('.update-loader').show();
            $('#control_overlay_shipping').show();
            $('#control_overlay_payment').show();
        },
        hideOverlay: function () {
            $('.update-loader').hide();
            $('#control_overlay_shipping').hide();
            $('#control_overlay_payment').hide();
        },
        updateQty: function (itemId, type, qty) {
            var params = {itemId: itemId, qty: qty, updateType: type};
            var self = this;
            this.showOverlay();
            $.ajax({
                url: updateQuote_url,
                type: 'POST',
                data: params,
                dataType: 'json'
            }).done(function (result) {
            }).fail(function (result) {
            }).always(function (result) {
                if (result.error) {
                    alertPopup({
                        content: Translate(result.error),
                        autoOpen: true,
                        clickableOverlay: true,
                        focus: "Something went wrong while update item",
                        actions: {
                            always: function () {
                            }
                        }
                    });
                }

                if (result.cartEmpty || result.is_virtual) {
                    window.location.reload();
                } else {
                    if (result.giftwrap_amount_final && !result.error) {
                        giftWrapModel.setGiftWrapAmount(result.giftwrap_amount_final);
                    }
                    if (result.rewardpointsEarning) {
                        $('tr.rewardpoint-earning td.amount span').text(result.rewardpointsEarning);
                    }
                    if (result.rewardpointsSpending) {
                        $('tr.rewardpoint-spending td.amount span').text(result.rewardpointsSpending);
                    }
                    if (result.rewardpointsUsePoint) {
                        $('tr.rewardpoint-use_point td.amount span').text(result.rewardpointsUsePoint);
                    }
                    if (result.affiliateDiscount) {
                        $('tr td.amount span').each(function () {
                            if ($(this).data('th') == Translate('Affiliateplus Discount')) {
                                if (result.affiliateDiscount) {
                                    $(this).text(priceUtils.formatPrice(result.affiliateDiscount, quote.getPriceFormat()));
                                    $(this).show();
                                } else {
                                    $(this).hide();
                                }
                            }
                        })
                    }
                    if (result.getRulesJson && window.checkoutConfig.isCustomerLoggedIn) {
                        var rewardSliderRules = $.parseJSON(result.getRulesJson).rate;
                        var $range = $("#range_reward_point");
                        var rewardpointConfig = result;
                        rewardpointConfig.checkMaxpoint = parseInt(rewardpointConfig.checkMaxpoint);
                        if (rewardpointConfig.checkMaxpoint) {
                            self.updateTotal(rewardSliderRules.sliderOption.maxPoints);
                            $('#reward_sales_point').val(rewardSliderRules.sliderOption.maxPoints);
                        }
                        var slider = $range.data("ionRangeSlider");
                        slider.update({
                            grid: true,
                            grid_num: ((rewardSliderRules.sliderOption.maxPoints < 4) ? rewardSliderRules.sliderOption.maxPoints : 4),
                            min: rewardSliderRules.sliderOption.minPoints,
                            max: rewardSliderRules.sliderOption.maxPoints,
                            step: rewardSliderRules.sliderOption.pointStep,
                            from: ((rewardpointConfig.checkMaxpoint) ? rewardSliderRules.sliderOption.maxPoints : rewardpointConfig.usePoint),
                            onUpdate: function (data) {
                                if (rewardSliderRules.sliderOption.maxPoints == data.from) {
                                    $('#reward_max_points_used').attr('checked', 'checked');
                                } else {
                                    $('#reward_max_points_used').removeAttr('checked');
                                }
                                $("#reward_sales_point").val(data.from);
                                self.updateTotal(data.from);
                            }
                        });
                    }
                    reloadShippingMethod();
                    self.showOverlay();
                    getPaymentInformation().done(function () {
                        self.hideOverlay();
                    });
                }
            });
        }
    });
});