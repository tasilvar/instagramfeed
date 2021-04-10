{*
 * Module mjinstagramfeed
 * @author MAGES Michał Jendraszczyk
 * @copyright (c) 2020, MAGES Michał Jendraszczyk
 * @license http://mages.pl MAGES Michał Jendraszczyk
*}

{if $status == '1'}
<div class="alert alert-success">
    Pomyślnie dodano zdjęcia z Instagram
</div>
{else}
    <div class="alert alert-danger">
    Nie dodano zdjęć - problem z autoryzacją
</div>
    {/if}