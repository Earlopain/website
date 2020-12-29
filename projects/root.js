let terminal = new Terminal("terminal-container");
const def = {
    "Some Stuff": {
        "Deezer Music": "https://www.deezer.com/en/profile/2248679704/albums",
        "Github": "https://gitlab.com/Earlopain/",
        "Youtube Playlist": "https://www.youtube.com/playlist?list=PLKh1uwoDbFYyBXb9ghqFPg66FS95TI3Ze"
    },
    "HDD": {
        "User": {
            "Desktop": {
                "Discord.lnk": "",
                "Firefox.lnk": "",
                "note.txt": "",
                "Steam.lnk": "",
                "wallpaper.png": ""
            },
            "Homework.doc": "",
            "Steam.exe": "",
            "Viruzz.exe": ""
        },
        "Windows": {
            "System32": {
                "explorer.dll": "",
                "kernel32.dll": ""
            },
            "boot.bin": "",
            "iexplorer.exe": "",
            "login.log": "",
            "password.txt": "",
            "user.cfg": ""
        }
    },
    "About": {
        "My Steam": "https://steamcommunity.com/id/earlopain",
        "Gitlab": "https://gitlab.com/Earlopain/Website",
        "Email": "mailto:earlopain@c5h8no4na.net"
    }
};
const projects = {
    "Visualization": "/projects/visualization",
    "owotext": "/projects/owotext",
    "e621history": "/projects/e621history",
    "humblecompare": "/projects/steamgames"
}
//def["Projects"] = projects;
terminal.parseFromJson(def);
terminal.finish();
