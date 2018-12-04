const faces = ["(・`ω´・)", ";;w;;", "owo", "UwU", ">w<", "^w^"];


function convertText(text) {
    text = text.replace(/(?:r|l)/g, "w");
    text = text.replace(/(?:R|L)/g, "W");
    text = text.replace(/n([aeiou])/g, 'ny$1');
    text = text.replace(/N([aeiou])/g, 'Ny$1');
    text = text.replace(/N([AEIOU])/g, 'Ny$1');
    text = text.replace(/ove/g, "uv");
    text = text.replace(/\!+/g, " " + faces[Math.floor(Math.random() * faces.length)] + " ");
    return text;
}
