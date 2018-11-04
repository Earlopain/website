let terminal = new Terminal("terminal-container");
terminal.startFolder("Some Stuff");
terminal.addFile("Deezer Music", "href", "https://www.deezer.com/en/profile/2248679704/albums");
terminal.addFile("Github", "href", "https://gitlab.com/Earlopain/");
terminal.addFile("Youtube Playlist", "href", "https://www.youtube.com/playlist?list=PLKh1uwoDbFYyBXb9ghqFPg66FS95TI3Ze");
terminal.endFolder();
terminal.startFolder("HDD");
terminal.startFolder("User");
terminal.startFolder("Desktop");
terminal.addFile("Discord.lnk");
terminal.addFile("Explorer.lnk");
terminal.addFile("Firefox.lnk");
terminal.addFile("note.txt");
terminal.addFile("Steam.lnk");
terminal.addFile("wallpaper.png");
terminal.endFolder();
terminal.addFile("Homework.doc");
terminal.addFile("Steam.exe");
terminal.addFile("Viruzz.exe");
terminal.endFolder();
terminal.startFolder("Windows");
terminal.startFolder("System32");
terminal.addFile("explorer.dll");
terminal.addFile("kernel32.dll");
terminal.endFolder();
terminal.addFile("boot.bin");
terminal.addFile("iexplorer.exe");
terminal.addFile("login.log");
terminal.addFile("password.txt");
terminal.addFile("user.cfg");
terminal.endFolder();
terminal.endFolder();
terminal.startFolder("Projects");
terminal.addFile("Visualization", "href", "/projects/visualization/discord.html");
terminal.endFolder();
terminal.startFolder("About");
terminal.addFile("My Steam", "href", "https://steamcommunity.com/id/earlopain/");
//terminal.addFile("Discord Server", "href", "https://discord.gg/twuvVWq");
terminal.addFile("Email", "href", "mailto:earlopain@c5h8no4na.net");
terminal.endFolder();
terminal.finish();