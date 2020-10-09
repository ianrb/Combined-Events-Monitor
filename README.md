# CEM

Combined Events Monitor was created as a means to combine DSD+, WAV file recordings, rtl_433 JSON logs and more into remotely accessible, simple web page.

This project lacks full documentation and is offered for review and information. I’ll do my best to make updates – but this is only one side-project of many.


A few prerequisites are Apache2, PHP 7, rtl_fm, sox, play, Ratchet - http://socketo.me/


Features:

- Mobile Friendly
- Automatically play new audio events
- Interruption free when one source is playing another will not overtake
- Recent events can be re/played by clicking on individual event
- Download .WAV by holding down on any event for >3 seconds
- Mute specific sources
- New Events are highlighted until and time refereed in seconds ago until the duration of call is complete in which case the call is considered ‘read’ and highlighting is removed and date stamp matches older events


- Options -> Mute All is self explanatory.
 

Notes specific to software

DSD+ 
- You will need to supply a copy of DSD+ and configure directories in your environment and this project.

Sox and Play 
- Required for file events, can be installed by using sudo apt-get install sox


rtl_433
- Often door sensors send out repeated events for one occurrence which floods the messages with unnecessary info and makes it hard to see other events. As as a simple fix, any event with the same id, state and within the same 3 seconds are excluded.
