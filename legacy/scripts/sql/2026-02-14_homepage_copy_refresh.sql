-- Homepage copy refresh for the default site language.
-- Safe to run multiple times in local, staging, and production.

START TRANSACTION;

SET @default_language_id := (
    SELECT id
    FROM languages
    WHERE default_lang = 1
    ORDER BY id
    LIMIT 1
);
SET @default_language_id := COALESCE(@default_language_id, 1);

-- Hero copy
INSERT INTO home_section (
    section_id,
    language_id,
    section_heading,
    section_short_heading
) VALUES (
    1,
    @default_language_id,
    'Turn Ideas Into Delivered Results.',
    'Trusted freelance services, right when you need them.'
)
ON DUPLICATE KEY UPDATE
    language_id = VALUES(language_id),
    section_heading = VALUES(section_heading),
    section_short_heading = VALUES(section_short_heading);

-- Marketplace cards copy
INSERT INTO home_cards (
    card_id,
    language_id,
    card_title,
    card_desc,
    card_link,
    card_image,
    isS3
) VALUES
    (1, @default_language_id, 'Logo Design', 'Build a brand that gets noticed.', 'categories/graphics-design/logo-design', '1.jpg', 0),
    (2, @default_language_id, 'Social Media', 'Reach more of the right customers.', 'categories/digital-marketing/social-media-marketing', '2.jpg', 0),
    (3, @default_language_id, 'Voice Talent', 'Find the right voice for your message.', 'categories/video-animation', '7.jpg', 0),
    (4, @default_language_id, 'Translation', 'Communicate clearly across languages.', 'categories/writing-translation/translation', '4.jpg', 0),
    (5, @default_language_id, 'Illustration', 'Bring your ideas to life visually.', 'categories/graphics-design/illustration', '5.jpg', 0),
    (6, @default_language_id, 'Photoshop Expert', 'Work with skilled retouching specialists.', 'categories/graphics-design/photoshop-editing', '6.jpg', 0)
ON DUPLICATE KEY UPDATE
    language_id = VALUES(language_id),
    card_title = VALUES(card_title),
    card_desc = VALUES(card_desc),
    card_link = VALUES(card_link),
    card_image = IF(card_image IS NULL OR card_image = '', VALUES(card_image), card_image),
    isS3 = COALESCE(isS3, VALUES(isS3));

-- Trust and workflow boxes
INSERT INTO section_boxes (
    box_id,
    language_id,
    box_title,
    box_desc,
    box_image,
    isS3
) VALUES
    (4, @default_language_id, 'Your Terms', 'Set the scope, budget, and priorities that fit your goals.', 'time.png', 0),
    (5, @default_language_id, 'Your Timeline', 'Choose delivery windows that match your deadlines.', 'desk.png', 0),
    (6, @default_language_id, 'Your Security', 'Protected payments and clear order tracking from start to finish.', 'tv.png', 0)
ON DUPLICATE KEY UPDATE
    language_id = VALUES(language_id),
    box_title = VALUES(box_title),
    box_desc = VALUES(box_desc),
    box_image = IF(box_image IS NULL OR box_image = '', VALUES(box_image), box_image),
    isS3 = COALESCE(isS3, VALUES(isS3));

COMMIT;
