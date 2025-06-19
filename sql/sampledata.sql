-- sampledata.sql - Sample Data for Simplified E-commerce Platform
-- This file contains sample data for the simplified marketplace which was generated on ai

-- Ensure we're using the correct database
USE ecomm_platform;

-- Clear existing data safely (if any)
SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM cart_items;
DELETE FROM purchase_requests;
DELETE FROM favorites;
DELETE FROM reviews;
DELETE FROM messages;
DELETE FROM product_images;
DELETE FROM products;
DELETE FROM categories;
DELETE FROM users;
SET FOREIGN_KEY_CHECKS = 1;

-- Reset auto-increment counters for clean data
ALTER TABLE users AUTO_INCREMENT = 1;
ALTER TABLE categories AUTO_INCREMENT = 1;
ALTER TABLE products AUTO_INCREMENT = 1;
ALTER TABLE product_images AUTO_INCREMENT = 1;
ALTER TABLE messages AUTO_INCREMENT = 1;
ALTER TABLE reviews AUTO_INCREMENT = 1;
ALTER TABLE favorites AUTO_INCREMENT = 1;
ALTER TABLE purchase_requests AUTO_INCREMENT = 1;
ALTER TABLE cart_items AUTO_INCREMENT = 1;

-- =====================================================
-- SAMPLE DATA FOR SIMPLIFIED MARKETPLACE
-- =====================================================

-- Users with simplified location field
-- Password for all users: 'password123' (properly hashed)
INSERT INTO users (name, email, password, phone, role, status, location) VALUES
-- System Administrators
('Sarah Naidoo', 'admin@marketplace.co.za', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', '011 123 4567', 3, 'active', 'Johannesburg, Gauteng'),
('Michael Stevens', 'mike.admin@marketplace.co.za', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', '021 234 5678', 3, 'active', 'Cape Town, Western Cape'),

-- Regular Users - Diverse South African Community
-- All have role=1 and can both buy and sell items
('Thabo Molefe', 'thabo.molefe@gmail.com', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', '072 234 5678', 1, 'active', 'Sandton, Johannesburg'),
('Emma van der Merwe', 'emma.vandermerwe@outlook.com', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', '073 345 6789', 1, 'active', 'Sea Point, Cape Town'),
('Sipho Ndlovu', 'sipho.ndlovu@yahoo.com', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', '074 456 7890', 1, 'active', 'Umhlanga, Durban'),
('Aisha Patel', 'aisha.patel@gmail.com', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', '075 567 8901', 1, 'active', 'Hatfield, Pretoria'),
('Jacobus Botha', 'kobus.botha@webmail.co.za', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', '076 678 9012', 1, 'active', 'Stellenbosch, Western Cape'),
('Nomsa Dlamini', 'nomsa.dlamini@hotmail.com', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', '077 789 0123', 1, 'active', 'Rosebank, Johannesburg'),
('Priya Maharaj', 'priya.maharaj@gmail.com', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', '078 890 1234', 1, 'active', 'Westville, Durban'),
('David Mthembu', 'david.mthembu@yahoo.co.za', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', '079 901 2345', 1, 'active', 'Centurion, Pretoria'),
('Fatima Abrahams', 'fatima.abrahams@outlook.com', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', '080 012 3456', 1, 'active', 'Claremont, Cape Town'),
('Johann Pretorius', 'johann.pretorius@telkomsa.net', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', '081 123 4567', 1, 'active', 'Fourways, Johannesburg'),
('Lerato Motsepe', 'lerato.motsepe@gmail.com', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', '082 234 5678', 1, 'active', 'Randburg, Johannesburg'),
('Tariq Hassan', 'tariq.hassan@mweb.co.za', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', '083 345 6789', 1, 'active', 'Bellville, Cape Town'),
('Chantelle Williams', 'chantelle.williams@vodamail.co.za', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', '084 456 7890', 1, 'active', 'Sandton, Johannesburg'),
('Mandla Khumalo', 'mandla.khumalo@gmail.com', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', '085 567 8901', 1, 'active', 'Morningside, Durban');

-- Categories for marketplace
INSERT INTO categories (name, description, parent_id, display_order) VALUES
-- Main categories for everyday marketplace
('Electronics', 'Electronic devices and accessories', NULL, 1),
('Home & Living', 'Furniture, appliances and household items', NULL, 2),
('Fashion & Style', 'Clothing, shoes and accessories', NULL, 3),
('Books & Entertainment', 'Books, games, movies and hobby items', NULL, 4),
('Sports & Outdoor', 'Sports gear and outdoor activities', NULL, 5),
('Vehicles & Transport', 'Cars, bikes, scooters and transport', NULL, 6),
('Health & Beauty', 'Health products and beauty items', NULL, 7),
('Services & Skills', 'Freelance services and skill sharing', NULL, 8),

-- Electronics subcategories
('Mobile Phones', 'Smartphones and mobile accessories', 1, 1),
('Computers', 'Laptops, desktops and accessories', 1, 2),
('Gaming', 'Consoles, games and entertainment', 1, 3),
('Audio', 'Speakers, headphones and audio equipment', 1, 4),

-- Home & Living subcategories
('Furniture', 'Chairs, tables, beds and storage', 2, 1),
('Kitchen', 'Appliances, cookware and dining', 2, 2),
('Decor', 'Home decor and garden accessories', 2, 3),

-- Fashion subcategories
('Clothing', 'Casual and formal clothing', 3, 1),
('Accessories', 'Shoes, bags, jewelry and accessories', 3, 2),

-- Books & Entertainment subcategories
('Books', 'Fiction, non-fiction and magazines', 4, 1),
('Media', 'Movies, music and entertainment', 4, 2);

-- Marketplace products with simplified schema
INSERT INTO products (title, description, price, negotiable, condition_status, category_id, seller_id, location, stock, status, featured, views) VALUES
-- Electronics - casual style
('iPhone 14 Pro - Hardly Used!', 'Selling my iPhone 14 Pro 128GB in Space Black! Got it as a birthday gift but honestly prefer Android 😅 Used it for maybe 3 months max. Still has 98% battery health and comes with everything - original box, charger, unused earphones. Also throwing in a really nice leather case I bought for R800. No scratches, drops, or water damage. Perfect for someone who wants the latest iPhone without the crazy retail price!', 16999.00, 1, 'like_new', 9, 3, 'Sandton, Johannesburg', 1, 'approved', 1, 89),

('Gaming Setup - Moving Sale!', 'Epic gaming setup for sale! Moving overseas so need to sell everything 😭 This beast includes: Custom built PC (Ryzen 7, RTX 3070, 32GB RAM), 27" curved gaming monitor (165Hz - buttery smooth!), RGB mechanical keyboard, wireless gaming mouse, and a super comfy gaming chair. Perfect for someone wanting to get serious about gaming or streaming. Everything works perfectly and can run any game on max settings. Will consider selling separately if needed.', 25999.00, 1, 'good', 11, 4, 'Sea Point, Cape Town', 1, 'approved', 1, 156),

('MacBook Air M2 - Perfect for Work', 'My MacBook Air M2 in Silver! Been my daily driver for work but upgrading to a Pro model. 13-inch, 256GB storage, 8GB RAM. Battery still lasts the whole day easily. Great for office work, video calls, light photo editing. Comes with MagSafe charger and a protective sleeve. Small scuff on the bottom (see pics) but screen and keyboard are perfect. Ideal for anyone starting a new job or freelancing!', 17999.00, 1, 'good', 10, 5, 'Umhlanga, Durban', 1, 'approved', 0, 67),

-- Home & Living - everyday items
('IKEA Desk + Chair Combo', 'Selling my home office setup! IKEA desk (120cm x 60cm) with matching office chair. Used for working from home for about a year. Desk has some minor scratches from daily use but still super sturdy. Chair is really comfortable for long work sessions. Perfect for someone setting up a home office on a budget. Easy to disassemble for transport. Cash on collection preferred!', 1299.00, 1, 'good', 13, 6, 'Hatfield, Pretoria', 1, 'approved', 0, 43),

('3-Seater Couch - Very Comfy!', 'Grey 3-seater couch in great condition! Super comfortable for movie nights and lazy Sundays. Had it for 2 years but moving to a smaller place. Fabric is still in good shape, no tears or stains. Cushions are still firm and supportive. Comes from a pet-free, smoke-free home. Would suit any living room or apartment. You\'ll need a bakkie to collect - it\'s quite big but worth it!', 2899.00, 1, 'good', 13, 7, 'Stellenbosch, Western Cape', 1, 'approved', 0, 34),

-- Fashion & Style - casual items
('Nike Air Max 270 - Size 9', 'Nike Air Max 270s in black and white, size 9. Worn maybe 10 times max - just not my style anymore. Super comfortable for walking and gym. Still have the box and they\'re clean with no major scuffs. Perfect for someone who loves Nike sneakers. Retail for like R2500 new, so this is a steal!', 899.00, 1, 'like_new', 16, 8, 'Rosebank, Johannesburg', 1, 'approved', 0, 28),

('Ladies\' Handbag Collection', 'Cleaning out my closet! Selling 3 beautiful handbags together - one black leather shoulder bag, one brown crossbody, and one small evening clutch. All in great condition, just don\'t use them anymore since I mostly work from home now. Perfect for someone who likes to match bags with outfits. All three for one price!', 799.00, 1, 'good', 17, 9, 'Westville, Durban', 1, 'approved', 0, 22),

-- Books & Entertainment
('Self-Help Book Bundle', 'Collection of amazing self-help and motivation books! Includes "Atomic Habits", "The 7 Habits", "Rich Dad Poor Dad", and 5 others. All in good condition with minimal highlighting. These books literally changed my mindset about money and success. Perfect for someone wanting to level up their life. Much cheaper than buying individually!', 450.00, 1, 'good', 18, 10, 'Centurion, Pretoria', 1, 'approved', 0, 31),

('PlayStation 4 with Games', 'PS4 Slim 500GB with 2 controllers and 6 games! Games include FIFA 23, Call of Duty, GTA V, Spider-Man, and more. Console works perfectly, just upgraded to PS5. All games are original discs in good condition. Perfect for casual gaming or gift for someone. Controllers have minor wear but work great.', 3499.00, 1, 'good', 11, 11, 'Claremont, Cape Town', 1, 'approved', 1, 78),

-- Sports & Outdoor
('Mountain Bike - Weekend Rides', 'Giant mountain bike, 21-speed, perfect for trails and city rides! Used mainly for weekend adventures around Joburg. Gears shift smoothly, brakes work perfectly. Recently serviced at bike shop. Includes helmet, bike lock, and water bottle holder. Great for fitness or just exploring the city. Selling because moving to Cape Town.', 2299.00, 1, 'good', 5, 12, 'Fourways, Johannesburg', 1, 'approved', 0, 45),

('Gym Equipment Set', 'Home gym setup for sale! Includes adjustable dumbbells (up to 20kg each), yoga mat, resistance bands, and foam roller. Perfect for home workouts. Everything is in great condition, just don\'t have space anymore. Great for anyone wanting to stay fit from home without expensive gym fees!', 1199.00, 1, 'good', 5, 13, 'Randburg, Johannesburg', 1, 'approved', 0, 33),

-- Vehicles & Transport
('Scooter - City Transport', '125cc scooter perfect for getting around the city! Great on petrol, easy to park anywhere. Used for daily commute for 2 years. Has minor scratches but engine runs perfectly. License and papers all up to date. Perfect for someone wanting cheap, reliable transport. Helmet included!', 8999.00, 1, 'good', 6, 14, 'Bellville, Cape Town', 1, 'approved', 0, 56),

-- Health & Beauty
('Skincare Bundle - Unopened', 'Decluttering my skincare collection! Bundle includes CeraVe cleanser, Neutrogena moisturizer, The Ordinary serum, and SPF 50 sunscreen. All unopened except one item used twice. Perfect for someone wanting to start a good skincare routine without breaking the bank. All products suit sensitive skin.', 399.00, 0, 'new', 7, 15, 'Sandton, Johannesburg', 1, 'approved', 0, 19),

-- Services & Skills
('Photography Session - Portraits', 'Offering professional portrait photography sessions! Perfect for LinkedIn profiles, family photos, or social media. Includes 1-hour session and 10 edited high-res photos. I have 3 years experience and professional camera equipment. Can shoot outdoors or at your location. Check my Instagram @photosbytariq for examples!', 899.00, 1, 'new', 8, 13, 'Bellville, Cape Town', 1, 'approved', 0, 27),

-- Additional casual items
('Coffee Machine - Barely Used', 'Nespresso coffee machine with milk frother! Got it as a gift but I\'m more of a tea person 😅 Used maybe 5 times max. Comes with original box, manual, and some coffee pods to get you started. Perfect for coffee lovers who want cafe-quality drinks at home. Works like new!', 1299.00, 1, 'like_new', 14, 16, 'Morningside, Durban', 1, 'approved', 0, 24);

-- Purchase requests showing real marketplace activity
INSERT INTO purchase_requests (buyer_id, seller_id, product_id, quantity, total_amount, buyer_message, status, delivery_method) VALUES
(4, 3, 1, 1, 16999.00, 'Can we meet in Cape Town this weekend?', 'pending', 'pickup'),
(5, 4, 2, 1, 25999.00, 'Very interested! Can you send more pics of the setup?', 'pending', 'delivery'),
(6, 5, 3, 1, 17999.00, 'Perfect for my new freelance work!', 'accepted', 'pickup'),
(7, 6, 4, 1, 1299.00, 'Thanks! Desk fits perfectly in my home office', 'completed', 'pickup'),
(8, 9, 7, 1, 799.00, 'Love the handbags! When can I collect?', 'pending', 'pickup'),
(9, 10, 8, 1, 450.00, 'Amazing books - exactly what I needed!', 'completed', 'pickup'),
(10, 11, 9, 1, 3499.00, 'Perfect for the kids! Can collect this weekend', 'accepted', 'pickup'),
(11, 12, 10, 1, 2299.00, 'Bike looks perfect for weekend rides!', 'pending', 'delivery');

-- Product images
INSERT INTO product_images (product_id, image_path, display_order) VALUES
(1, 'uploads/products/iphone14_pro_1.jpg', 0),
(1, 'uploads/products/iphone14_pro_2.jpg', 1),
(2, 'uploads/products/gaming_setup_1.jpg', 0),
(2, 'uploads/products/gaming_setup_2.jpg', 1),
(3, 'uploads/products/macbook_air_m2_1.jpg', 0),
(4, 'uploads/products/ikea_desk_chair.jpg', 0),
(5, 'uploads/products/grey_couch_1.jpg', 0),
(5, 'uploads/products/grey_couch_2.jpg', 1),
(6, 'uploads/products/nike_air_max_270.jpg', 0),
(7, 'uploads/products/handbag_collection.jpg', 0),
(8, 'uploads/products/self_help_books.jpg', 0),
(9, 'uploads/products/ps4_games_1.jpg', 0),
(9, 'uploads/products/ps4_games_2.jpg', 1),
(10, 'uploads/products/mountain_bike_1.jpg', 0),
(11, 'uploads/products/gym_equipment.jpg', 0),
(12, 'uploads/products/city_scooter.jpg', 0),
(13, 'uploads/products/skincare_bundle.jpg', 0),
(14, 'uploads/products/photography_portfolio.jpg', 0),
(15, 'uploads/products/nespresso_machine.jpg', 0);

-- Reviews from satisfied customers
INSERT INTO reviews (product_id, user_id, rating, comment, created_at) VALUES
(4, 7, 5, 'Perfect desk and chair combo! Seller was super helpful with delivery and everything was exactly as described. Great value for money!', DATE_SUB(NOW(), INTERVAL 10 DAY)),
(8, 9, 5, 'Amazing book collection! Already started reading Atomic Habits and loving it. All books in great condition as promised.', DATE_SUB(NOW(), INTERVAL 8 DAY)),
(9, 10, 4, 'PS4 works perfectly and kids love the games! Minor controller wear but nothing major. Good deal overall.', DATE_SUB(NOW(), INTERVAL 15 DAY)),
(3, 6, 5, 'MacBook is perfect for my freelance work! Battery life is excellent and seller was honest about the small scuff. Highly recommend!', DATE_SUB(NOW(), INTERVAL 12 DAY)),
(10, 11, 4, 'Great bike for the price! Recently serviced as mentioned and rides smoothly. Perfect for my weekend adventures.', DATE_SUB(NOW(), INTERVAL 20 DAY)),
(1, 4, 5, 'iPhone is in amazing condition! 98% battery health as promised and the leather case is really nice quality. Very happy!', DATE_SUB(NOW(), INTERVAL 18 DAY));

-- Messages between users (Real marketplace conversations)
INSERT INTO messages (sender_id, receiver_id, product_id, subject, message, read_status, created_at) VALUES
(4, 3, 1, 'iPhone 14 Pro Question', 'Hi! Is the iPhone still available? The leather case looks really nice in the photos. Can we meet somewhere in Cape Town?', 1, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(3, 4, 1, 'Re: iPhone 14 Pro Question', 'Yes still available! The case is genuine leather, really good quality. I can meet anywhere in the city center. When works for you?', 1, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(5, 4, 2, 'Gaming Setup', 'Wow this setup looks incredible! Is everything included in the price? What games can it run smoothly?', 1, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(4, 5, 2, 'Re: Gaming Setup', 'Everything shown in the pics is included! Runs any game on max settings - Cyberpunk, FIFA, COD, everything. It\'s honestly overkill for most games lol', 1, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(6, 5, 3, 'MacBook Air', 'Hi! Perfect laptop for my freelance work. Is the small scuff very noticeable? And does it come with any software?', 1, DATE_SUB(NOW(), INTERVAL 7 DAY)),
(5, 6, 3, 'Re: MacBook Air', 'The scuff is tiny, you barely notice it! Comes with all the standard Mac software plus I can leave Adobe Creative Suite if you need it.', 1, DATE_SUB(NOW(), INTERVAL 7 DAY)),
(8, 9, 7, 'Handbag Collection', 'Love the handbags! Are they all real leather? What are the sizes like?', 0, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(9, 10, 8, 'Book Bundle', 'Perfect! I\'ve been wanting to read these for ages. Are they all in English?', 1, DATE_SUB(NOW(), INTERVAL 4 DAY)),
(10, 9, 8, 'Re: Book Bundle', 'Yes all in English and really good condition. The highlighting actually helps highlight the important parts!', 1, DATE_SUB(NOW(), INTERVAL 4 DAY)),
(11, 12, 10, 'Mountain Bike', 'Bike looks perfect for what I need! Does it come with the helmet and lock as mentioned?', 1, DATE_SUB(NOW(), INTERVAL 6 DAY)),
(12, 11, 10, 'Re: Mountain Bike', 'Yes! Helmet, lock, and water bottle holder all included. The helmet is basically new, only worn a few times.', 1, DATE_SUB(NOW(), INTERVAL 6 DAY));

-- Favorites (users saving products for later)
INSERT INTO favorites (user_id, product_id) VALUES
(4, 2), -- Emma likes gaming setup
(5, 12), -- Sipho likes the scooter
(6, 11), -- Aisha likes gym equipment
(7, 13), -- Jacobus likes skincare bundle
(8, 15), -- Nomsa likes coffee machine
(9, 14), -- Priya likes photography service
(10, 7); -- David likes handbag collection

-- Cart items (users browsing for purchases)
INSERT INTO cart_items (user_id, product_id, quantity) VALUES
(4, 2, 1), -- Emma interested in gaming setup
(5, 12, 1), -- Sipho wants the scooter
(6, 11, 1), -- Aisha looking at gym equipment
(7, 13, 1), -- Jacobus interested in skincare bundle
(8, 15, 1), -- Nomsa wants coffee machine
(9, 14, 1), -- Priya interested in photography service
(10, 7, 1); -- David looking at handbag collection

-- Success message
SELECT 'Simplified marketplace sample data loaded successfully! Ready for casual buying and selling.' as status;

/*
=== LOGIN CREDENTIALS ===
Password for all accounts: 'password123'

Admin Accounts:
- admin@marketplace.co.za (Sarah Naidoo)
- mike.admin@marketplace.co.za (Michael Stevens)

Regular User Accounts (All can buy AND sell):
- thabo.molefe@gmail.com (Thabo Molefe)
- emma.vandermerwe@outlook.com (Emma van der Merwe)
- sipho.ndlovu@yahoo.com (Sipho Ndlovu)
- aisha.patel@gmail.com (Aisha Patel)
- kobus.botha@webmail.co.za (Jacobus Botha)
- nomsa.dlamini@hotmail.com (Nomsa Dlamini)
- priya.maharaj@gmail.com (Priya Maharaj)
- david.mthembu@yahoo.co.za (David Mthembu)
- fatima.abrahams@outlook.com (Fatima Abrahams)
- johann.pretorius@telkomsa.net (Johann Pretorius)
- lerato.motsepe@gmail.com (Lerato Motsepe)
- tariq.hassan@mweb.co.za (Tariq Hassan)
- chantelle.williams@vodamail.co.za (Chantelle Williams)
- mandla.khumalo@gmail.com (Mandla Khumalo)

=== SIMPLIFIED MARKETPLACE FEATURES ===
Database includes:
- 15 users with unified buy/sell capabilities
- Simplified location system (single text field)
- Casual marketplace categories and subcategories
- Diverse everyday products with informal descriptions
- Realistic pricing for various budgets
- Sample transactions between users
- Real marketplace messaging conversations
- Favorites and cart functionality
- Photography services and other skills

All data demonstrates:
- Unified user model (no separate buyer/seller roles)
- Simplified location handling
- Casual, informal marketplace functionality
- Real-world South African context
- Competitive pricing and product variety
- Modern e-commerce principles with student-friendly complexity

Perfect for a simplified marketplace website!
*/
