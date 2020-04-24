const { readFileSync, writeFileSync, copyFileSync } = require('fs');

// README.md
let readme = readFileSync(`${__dirname}/README.md`).toString();
readme = readme.replace(/\[\[toc]]\n{2}/g, '');
readme = readme.replace(
  /\[([a-zA-Z0-9 ]+)]\(\/([a-zA-Z0-9-\/.#]+)\)/g,
  '[$1](https://mathieu-bour.github.io/guardian/$2)'
);

writeFileSync(`${__dirname}/../README.md`, readme);

// CONTRIBUTING.md
copyFileSync(`${__dirname}/contributing.md`, `${__dirname}/../CONTRIBUTING.md`)
