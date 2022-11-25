{**
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License version 3.0
* that is bundled with this package in the file LICENSE.txt
* It is also available through the world-wide-web at this URL:
* https://opensource.org/licenses/AFL-3.0
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to a newer
* versions in the future. If you wish to customize this module for your needs
* please refer to CustomizationPolicy.txt file inside our module for more information.
*
* @author Webkul IN
* @copyright Since 2010 Webkul
* @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
*}

{if Configuration::get('WK_MP_REVIEW_HELPFUL_SETTINGS')}
<div class="wk_review_like">
    <div class="wk_pull_left">
        <strong>{l s='Is this review helpful to you?' mod='marketplace'}</strong>
    </div>
    <div class="wk_pull_left">
        <a href="#" data-toggle="modal" data-target="#wk_feedback_model">
            <div class="wk_like_action wk_icon_{$review.id_review}" data-id-review="{$review.id_review}" {if isset($review.like) && $review.like == '1'}style="background-color:#30A728;"{/if}>
                {l s='Yes' mod='marketplace'}
            </div>
        </a>
    </div>
    <div class="wk_pull_left wk_helpful_number">
        <span class="wk_like_number_{$review.id_review}">
            {if isset($review.total_likes)}{$review.total_likes}{else}0{/if}
        </span>
    </div>
    <div class="wk_pull_left">
        <a href="#" data-toggle="modal" data-target="#wk_feedback_model">
            <div class="wk_dislike_action wk_icon_{$review.id_review}" data-id-review="{$review.id_review}" {if isset($review.like) && $review.like == '0'}style="background-color:#E23939;"{/if}>
                {l s='No' mod='marketplace'}
            </div>
        </a>
    </div>
    <div class="wk_pull_left wk_helpful_number">
        <span class="wk_dislike_number_{$review.id_review}">
            {if isset($review.total_dislikes)}{$review.total_dislikes}{else}0{/if}
        </span>
    </div>
    <div class="clearfix"></div>
</div>

{if empty($logged)}
    <div class="modal fade" id="wk_feedback_model" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">
                        {l s='Please login to give your feedback.' mod='marketplace'}
                    </h4>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn wk_btn_cancel wk_btn_extra" data-dismiss="modal">{l s='Cancel' mod='marketplace'}</button>
                    <a href="{$myAccount}">
                        <button type="button" class="btn btn-success wk_btn_extra">{l s='Login' mod='marketplace'}</button>
                    </a>
                </div>
            </div>
        </div>
    </div>
{/if}
{/if}