const fs = require('fs');
const path = require('path');

const dir = path.join(__dirname, 'resources', 'js', 'Pages', 'Reports');
const files = fs.readdirSync(dir).filter(f => f.endsWith('.jsx'));

files.forEach(file => {
    const filePath = path.join(dir, file);
    let content = fs.readFileSync(filePath, 'utf8');
    
    // Add target="_blank" to PDF and Excel links if missing
    content = content.replace(/<a href=\{route\('reports\.(.*?)\.(pdf|excel)'(.*?) className="/g, '<a target="_blank" href={route(\'reports.$1.$2\'$3 className="');
    
    fs.writeFileSync(filePath, content);
});
console.log('Done!');
