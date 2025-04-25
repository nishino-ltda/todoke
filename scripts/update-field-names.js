import fs from 'fs';
import path from 'path';
import { fieldMappings } from './field-name-mappings.js';

// Directories to process
const TARGET_DIRS = [
  'database/migrations',
  'app/Models',
  'app/Http/Controllers/API',
  'database/factories',
  'tests/Feature',
  'tests/Unit'
];

// File extensions to process
const TARGET_EXTENSIONS = ['.php'];

function processFile(filePath) {
  let content = fs.readFileSync(filePath, 'utf8');
  let changesMade = false;

  for (const [oldName, newName] of Object.entries(fieldMappings)) {
    // Create regex pattern to match the field name in various contexts
    const pattern = new RegExp(`(['"])${oldName}\\1`, 'g');
    if (pattern.test(content)) {
      content = content.replace(pattern, `$1${newName}$1`);
      changesMade = true;
    }
  }

  processedFiles++;
  if (changesMade) {
    try {
      fs.writeFileSync(filePath, content);
      console.log(`Updated: ${filePath}`);
      updatedFiles++;
    } catch (error) {
      console.error(`Error updating file ${filePath}:`, error);
    }
  }
}

function processDirectory(dir) {
  const files = fs.readdirSync(dir);

  for (const file of files) {
    const fullPath = path.join(dir, file);
    const stat = fs.statSync(fullPath);

    if (stat.isDirectory()) {
      processDirectory(fullPath);
    } else if (TARGET_EXTENSIONS.includes(path.extname(fullPath))) {
      processFile(fullPath);
    }
  }
}

// Run the script with error handling
console.log('Starting field name updates...');
let processedFiles = 0;
let updatedFiles = 0;

try {
  for (const dir of TARGET_DIRS) {
    if (!fs.existsSync(dir)) {
      console.warn(`Directory not found: ${dir}`);
      continue;
    }

    console.log(`Processing directory: ${dir}`);
    processDirectory(dir);
  }

  console.log(`\nField name update complete!`);
  console.log(`Processed ${processedFiles} files`);
  console.log(`Updated ${updatedFiles} files`);
} catch (error) {
  console.error('\nError during field name updates:');
  console.error(error);
  process.exit(1);
}
