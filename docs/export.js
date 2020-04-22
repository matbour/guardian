const { readFileSync, writeFileSync, copyFileSync } = require('fs');

// README.md
let readme = readFileSync(`${__dirname}/README.md`).toString();
readme = readme.replace(/\[\[toc]]\n{2}/g, '');
writeFileSync(`${__dirname}/../README.md`, readme);

// CONTRIBUTING.md
copyFileSync(`${__dirname}/contributing.md`, `${__dirname}/../CONTRIBUTING.md`)
