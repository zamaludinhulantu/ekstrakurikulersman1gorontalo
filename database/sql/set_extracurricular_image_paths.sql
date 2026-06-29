-- Run this after base data import to point extracurriculars to built-in images.

UPDATE extracurriculars
SET image_path = 'images/extracurriculars/pramuka.jpg'
WHERE name = 'Pramuka';

UPDATE extracurriculars
SET image_path = 'images/extracurriculars/paskibra.webp'
WHERE name = 'Paskibra';

UPDATE extracurriculars
SET image_path = 'images/extracurriculars/pmr.jpg'
WHERE name = 'PMR';

UPDATE extracurriculars
SET image_path = 'images/extracurriculars/rohis.jpg'
WHERE name = 'Rohis';

UPDATE extracurriculars
SET image_path = 'images/extracurriculars/quran-student.jpg'
WHERE name IN ('Tilawatil Qur''an', 'Tartil dan Hifzil Qur''an');

UPDATE extracurriculars
SET image_path = 'images/extracurriculars/student-discussion.jpg'
WHERE name IN ('OPSI', 'Menulis Artikel', 'Pelsis', 'SMAG', 'RELS', 'OSIS / MPK', 'PA/PI Duta', 'Fortina');

UPDATE extracurriculars
SET image_path = 'images/extracurriculars/student-camera.jpg'
WHERE name = 'Konten Kreator';

UPDATE extracurriculars
SET image_path = 'images/extracurriculars/student-parade.jpg'
WHERE name IN ('PBB/Paskib', 'PKS');

-- Optional cleanup if you decide to remove duplicate extracurricular name.
-- UPDATE extracurriculars
-- SET image_path = NULL
-- WHERE name IN ('Basket', 'Futsal');
