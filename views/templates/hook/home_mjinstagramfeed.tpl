</div>
<div class="full-width insta-block" style="margin: 40px 15px;">
    
    <div class="title-section text-center">
        <h1 class="title">
            <span>Instagram</span>
            <a href="{$instagram_profile}" target="_blank">{$user_name}</a>
        </h1>
    </div>
    {*{$instagram_feeds|print_r}*}
   {* <ul class="instagram-feed-block row">
        {foreach $instagram_feeds as $feed}
        <li class="col-xs-6 col-sm-3">
            <a href="{$feed['post_link']}" title="{$feed['title']}" target="_blank" style="background-size: cover;height: 350px;overflow: hidden;display: inline-block;background-image: url({$urls.shop_domain_url}{__PS_BASE_URI__}modules/mjinstagramfeed/img/{$feed['post_id']}.jpg)">
                <img src="{$urls.shop_domain_url}{__PS_BASE_URI__}modules/mjinstagramfeed/img/{$feed['post_id']}.jpg" alt="{$feed['title']}" style="opacity:0;">
            </a>
        </li>
        {/foreach}
    </ul>*}
    {if count($wyroznione_posty) > 0}
        {if $wyroznione_posty[0] != ''}
    <ul class="instagram-feed-block row">
        {foreach $wyroznione_posty as $post}
        <li class="col-xs-6 col-sm-3">
            <a href="{$post['post_link']}" title="{$post['title']}" target="_blank" style="background-size: cover;height: 350px;overflow: hidden;display: inline-block;background-image: url({$urls.shop_domain_url}{__PS_BASE_URI__}modules/mjinstagramfeed/img/{$post['post_id']}.jpg)">
                <img src="{$urls.shop_domain_url}{__PS_BASE_URI__}modules/mjinstagramfeed/img/{$post['post_id']}.jpg" alt="{$post['title']}" style="opacity:0;">
            </a>
        </li>
        {/foreach}
    </ul>
    {/if}
    {/if}
</div>
<div class="container">
