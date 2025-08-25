@component('mail::message')
# New pause request

Reason : {{ $demande->raison }}  
Release date : {{ $demande->dateSortiePause }}  
Release time : {{ $demande->dateSortie }}  
Return time : {{ $demande->dateRetour }}  

Thanks,<br>
{{ $demande->emailEmployee  }}
@endcomponent


