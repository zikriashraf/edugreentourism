-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 04, 2026 at 12:24 PM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `edugreentourism`
--

-- --------------------------------------------------------

--
-- Table structure for table `analytics_lists`
--

CREATE TABLE `analytics_lists` (
  `id` int NOT NULL,
  `category` enum('activity','destination') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `item_name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `order_num` int NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `analytics_lists`
--

INSERT INTO `analytics_lists` (`id`, `category`, `item_name`, `order_num`, `status`) VALUES
(1, 'activity', 'Hiking', 1, 1),
(2, 'activity', 'Visit Fig Farm', 2, 1),
(3, 'activity', 'Water Rafting', 3, 1),
(4, 'destination', 'Bukit Perangin', 1, 1),
(5, 'destination', 'Fig Farm', 2, 1),
(6, 'destination', 'Sungai Bil', 3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `analytics_stats`
--

CREATE TABLE `analytics_stats` (
  `id` int NOT NULL,
  `stat_key` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `stat_value` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `stat_label` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_visible` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `analytics_stats`
--

INSERT INTO `analytics_stats` (`id`, `stat_key`, `stat_value`, `stat_label`, `is_visible`) VALUES
(1, 'co2', '3', 'tonnes CO₂ saved', 1),
(2, 'tourists', '2,616', 'tourists involved', 1),
(3, 'trees', '4,729', 'trees & plants planted', 1),
(4, 'number_of_activities', '250', 'Number of Activities', 0);

-- --------------------------------------------------------

--
-- Table structure for table `attractions`
--

CREATE TABLE `attractions` (
  `attractions_id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci NOT NULL,
  `image_url` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `order_number` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int NOT NULL,
  `booking_type` enum('standard','custom') DEFAULT 'standard',
  `package_id` int DEFAULT NULL,
  `custom_activities` text,
  `customer_email` varchar(255) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_phone` varchar(50) NOT NULL,
  `booking_date` date NOT NULL COMMENT 'The date they selected to visit',
  `total_cost` decimal(10,2) NOT NULL,
  `pax_adults` int NOT NULL,
  `pax_children` int NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `status` varchar(20) DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `booking_type`, `package_id`, `custom_activities`, `customer_email`, `customer_name`, `customer_phone`, `booking_date`, `total_cost`, `pax_adults`, `pax_children`, `payment_method`, `status`, `created_at`) VALUES
(2, 'custom', NULL, '[\"1\",\"2\"]', 'ttt@gmail.com', 'zkoxixsnc', '0202849830', '2025-12-31', '60.00', 1, 0, 'Cash', 'Paid', '2025-12-13 08:10:16'),
(3, 'custom', NULL, '[\"1\",\"2\"]', 'ttt@gmail.com', 'zmxskxmk', '01293302129', '2026-01-08', '60.00', 1, 0, 'Cash', 'Paid', '2025-12-13 17:21:22'),
(4, 'custom', NULL, '[\"1\",\"2\"]', 'ttt@gmail.com', 'zkoxixsnc', '01293302129', '2026-01-09', '60.00', 1, 0, 'Cash', 'Paid', '2025-12-15 15:49:49'),
(5, 'custom', NULL, '[\"1\",\"2\"]', 'ttt@gmail.com', 'zmxskxmk', '01293302129', '2025-12-31', '60.00', 1, 0, 'ToyyibPay', 'Pending', '2025-12-22 16:43:33'),
(6, 'custom', NULL, '[\"1\",\"2\"]', 'ttt@gmail.com', 'zmxskxmk', '01293302129', '2025-12-31', '60.00', 1, 0, 'ToyyibPay', 'Pending', '2025-12-22 16:46:42'),
(8, 'custom', NULL, '[\"1\",\"2\"]', 'ttt@gmail.com', 'zmxskxmk', '01293302129', '2025-12-31', '60.00', 1, 0, 'ToyyibPay', 'Pending', '2025-12-22 17:10:16'),
(9, 'custom', NULL, '[\"1\",\"2\"]', 'ttt@gmail.com', 'zmxskxmk', '01293302129', '2025-12-30', '60.00', 1, 0, 'ToyyibPay', 'Pending', '2025-12-22 17:17:17'),
(10, 'custom', NULL, '[\"1\",\"2\"]', 'ttt@gmail.com', 'zmxskxmk', '01293302129', '2025-12-30', '60.00', 1, 0, 'ToyyibPay', 'Pending', '2025-12-22 17:18:03'),
(11, 'custom', NULL, '[\"1\",\"2\"]', 'ttt@gmail.com', 'zmxskxmk', '01293302129', '2026-01-10', '60.00', 1, 0, 'ToyyibPay', 'Pending', '2025-12-22 17:20:17'),
(13, 'custom', NULL, '[\"2\",\"3\"]', 'ttt@gmail.com', 'zmxskxmk', '01293302129', '2026-01-08', '15.00', 1, 0, 'ToyyibPay', 'Pending', '2025-12-26 16:28:07'),
(14, 'custom', NULL, '[\"1\",\"4\",\"5\"]', 'ttt@gmail.com', 'zkoxixsnc', '01293302129', '2026-01-07', '105.00', 1, 0, 'ToyyibPay', 'Pending', '2025-12-26 17:04:56'),
(15, 'custom', NULL, '[\"1\",\"4\",\"5\"]', 'ttt@gmail.com', 'zkoxixsnc', '01293302129', '2026-01-07', '105.00', 1, 0, 'ToyyibPay', 'Pending', '2025-12-26 17:05:31'),
(17, 'custom', NULL, '[\"1\",\"4\",\"5\"]', 'ttt@gmail.com', 'zkoxixsnc', '01293302129', '2026-01-08', '210.00', 1, 0, 'ToyyibPay', 'Pending', '2025-12-29 08:18:57'),
(22, 'standard', 104, NULL, 'ttt@gmail.com', 'zkoxixsnc', '01293302129', '2026-01-07', '150.00', 1, 0, 'Cash', 'Pending Payment', '2025-12-31 15:34:47'),
(23, 'standard', 105, NULL, 'ttt@gmail.com', 'zkoxixsnc', '01293302129', '2026-01-10', '180.00', 1, 0, 'Cash', 'Pending Payment', '2025-12-31 15:35:50'),
(29, 'custom', NULL, '[\"1\",\"2\",\"5\"]', 'budiman@gmai.com', 'budi', '01329584302', '2026-01-19', '65.00', 1, 0, 'Cash', 'Pending Payment', '2026-01-04 10:05:14');

-- --------------------------------------------------------

--
-- Table structure for table `contact_details`
--

CREATE TABLE `contact_details` (
  `contact_id` int NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `address` text COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_details`
--

INSERT INTO `contact_details` (`contact_id`, `email`, `phone`, `address`) VALUES
(1, 'edugreentourism@gmail.com', '+60 13-566-1044', 'Universiti Pendidikan Sultan Idris, Kampus Sultan Azlan Shah, Proton City, 35900 Tanjung Malim, Malaysia');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `message` text COLLATE utf8mb4_general_ci NOT NULL,
  `submitted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `submitted_at`) VALUES
(1, 'zikri ashraf', 'zikriashraf03@gmail.com', 'cvfbgyjh', 'jcxidvns fkxpkac ofvkvpkcpavm', '2025-12-14 16:11:23');

-- --------------------------------------------------------

--
-- Table structure for table `donations`
--

CREATE TABLE `donations` (
  `id` int NOT NULL,
  `donor_name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT 'Anonymous',
  `amount` decimal(10,2) NOT NULL,
  `date` date NOT NULL,
  `source` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'Manual'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `donations`
--

INSERT INTO `donations` (`id`, `donor_name`, `amount`, `date`, `source`) VALUES
(1, 'Anonymous', '7329.00', '2025-12-30', 'Manual'),
(2, 'zkoxixsnc (Checkout)', '1.00', '2025-12-30', 'Checkout'),
(3, 'zkoxixsnc (Checkout)', '1.00', '2025-12-30', 'Checkout');

-- --------------------------------------------------------

--
-- Table structure for table `explore_deeper`
--

CREATE TABLE `explore_deeper` (
  `id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `explore_deeper`
--

INSERT INTO `explore_deeper` (`id`, `title`, `content`, `status`) VALUES
(1, 'EXPLORE DEEPER', 'Malaysia’s National Green Policy provides the policy foundation for sustainable development, encouraging eco-friendly practices and conservation across sectors including tourism.\r\n\r\nThis aligns with UNESCO’s mission to safeguard cultural and natural heritage, ensuring that destinations of global value are preserved for future generations. Meanwhile, Geographic Information Systems (GIS) act as a powerful tool to support both policy and heritage protection by mapping eco-tourism sites, monitoring environmental impact, and guiding responsible tourism planning.\r\n\r\nTogether, these three pillars create a strong framework for advancing green tourism in a structured, inclusive, and technology-driven way.', 1);

-- --------------------------------------------------------

--
-- Table structure for table `explore_section`
--

CREATE TABLE `explore_section` (
  `explore_id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `content` text COLLATE utf8mb4_general_ci NOT NULL,
  `image_url` varchar(500) COLLATE utf8mb4_general_ci NOT NULL,
  `display_order` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `explore_section`
--

INSERT INTO `explore_section` (`explore_id`, `title`, `content`, `image_url`, `display_order`) VALUES
(1, 'Sungai Bil', 'Through its serene landscape and eco-friendly charm, Sungai Bil reminds us of the importance of protecting our natural treasures for future generations. Enjoy the peaceful sound of flowing water surrounded by forest serenity.\r\n<br><br>\r\n<i>\"The crystal-clear, icy cool water at Sungai Bil made for a perfect refreshing escape, and the surrounding greenery provided such a relaxing atmosphere for our family picnic.\"</i> - <b>Ahmad</b>', 'sungai_bil.jpg', 2),
(2, 'Bukit Perangin', 'A favorite destination for hikers and nature enthusiasts, Bukit Perangin offers a panoramic view of Tanjung Malim’s green heart. Standing at an elevation of 571 meters, the peak provides a rewarding challenge that ends with a breathtaking vista of the landscape below.\r\n<br><br>\r\n<i>\"The hike up Bukit Perangin was exhilarating, reaching the 571m summit was a bit of a workout, but the cool breeze and stunning view at the top made every step worth it!\"</i>  <b>- Fatin</b>', 'bukit_perangin.jpg', 3),
(3, 'Fig Muallim Farm', 'A local agro-tourism gem that blends sustainability and education. Visitors can learn about eco-farming, taste organic figs, and support local farmers committed to preserving the environment.\r\n<br><br>\r\n<i>\"The fresh figs were incredibly sweet, and the tour was so educational. It’s amazing to see sustainable farming in action right here in Tanjung Malim!\"</i> - <b>Hassan</b>', 'fig_muallim.jpeg', 4),
(4, 'UPSI Adventure Park', 'Experience outdoor education and environmental awareness in one location. From team-building to forest trails, UPSI Adventure Park fosters appreciation of nature through experiential learning.\r\n<br><br>\r\n<i>\"A fantastic place for our team building event! The obstacle courses were challenging but fun, and the guides made sure we learned about the forest ecosystem along the way.\"</i> <b>- Haziq</b>', 'UAP.jpg', 5),
(6, 'Discover Eco-Attractions', 'Discovering Tanjung Malim’s eco-attractions is more than just sightseeing — it’s an opportunity to connect with nature, support local communities, and gain meaningful knowledge about green living and conservation.', 'teratak.jpg', 1),
(7, 'Orang Asli Retreat: Kampung Chinggung', 'Immerse yourself in the authentic lifestyle of the Orang Asli at Kampung Chinggung. This retreat offers a unique connection to nature, where visitors can learn traditional survival skills, explore the jungle, and experience the warmth of the indigenous community.\r\n<br><br>\r\n<i>\"The hospitality was heartwarming. It was a privilege to learn about the jungle directly from the Orang Asli guides—a truly grounding and unforgettable experience.\"</i> <b>- Kamal</b>', 'chinggung.jpg', 6),
(8, 'Whitewater Rafting at Ulu Slim River', 'Get your adrenaline pumping with an exhilarating white water rafting adventure. Navigate the rapids of the river while surrounded by lush rainforest, a perfect mix of excitement and appreciation for Malaysia\'s wild rivers.\r\n<br><br>\r\n<i>\"Absolutely thrilling! The guides were professional and kept us safe while ensuring we had the time of our lives crashing through the rapids. A must-do for adventure seekers!\"</i> <b>- Michael T.</b>', 'ulu slim.jpg', 7);

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `feedback_id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `rating` int NOT NULL,
  `message` text COLLATE utf8mb4_general_ci NOT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`feedback_id`, `name`, `rating`, `message`, `status`) VALUES
(1, 'Mr. Rahman', 5, 'Fantastic experience! The green tourism program in Tanjung Malim opened my eyes to sustainable travel.', 1),
(2, 'Miss Aifa', 3, 'Good initiative but could use more organized scheduling.', 1),
(3, 'Mr. Abdul', 1, 'The website looked promising but lacked information for first-time visitors.', 1);

-- --------------------------------------------------------

--
-- Table structure for table `gallery_images`
--

CREATE TABLE `gallery_images` (
  `image_id` int NOT NULL,
  `image_url` varchar(500) COLLATE utf8mb4_general_ci NOT NULL,
  `caption` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `order_number` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gallery_images`
--

INSERT INTO `gallery_images` (`image_id`, `image_url`, `caption`, `order_number`) VALUES
(1, 'sungai_bil.jpg', 'Waterfall', 1),
(2, 'bukit_perangin.jpg', 'Bridge view', 2),
(3, 'fig_muallim.jpeg', 'Fruit farm', 3),
(4, 'UAP.jpg', 'River rafting', 4),
(5, 'muziumupsi.jpeg', 'Muzium Pendidikan Nasional', 5);

-- --------------------------------------------------------

--
-- Table structure for table `hero_slides`
--

CREATE TABLE `hero_slides` (
  `slide_id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci NOT NULL,
  `image_url` varchar(500) COLLATE utf8mb4_general_ci NOT NULL,
  `order_number` int NOT NULL,
  `status` tinyint(1) NOT NULL COMMENT 'Default:1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hero_slides`
--

INSERT INTO `hero_slides` (`slide_id`, `title`, `description`, `image_url`, `order_number`, `status`) VALUES
(10, 'Tanjung Malim Nature Trail', 'Discover the serene natural beauty of Tanjung Malim’s green landscapes — a peaceful retreat for eco-travelers.', 'img/banjaran titiwangsa.jpg', 1, 1),
(11, 'Lata Sungai Bil', 'Enjoy the refreshing waterfall of Lata Sungai Bil — a green destination for families and nature lovers alike.', 'img/river.jpg', 2, 1),
(12, 'Bukit Perangin', 'Take an eco-hike at Bukit Perangin and experience the lush greenery that defines Tanjung Malim’s sustainable tourism.', 'img/bukit perangin2.webp', 3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `learn_content`
--

CREATE TABLE `learn_content` (
  `sections_id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `content` longtext COLLATE utf8mb4_general_ci NOT NULL,
  `image_url` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `video_url` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `order_number` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `learn_content`
--

INSERT INTO `learn_content` (`sections_id`, `title`, `content`, `image_url`, `video_url`, `order_number`) VALUES
(1, 'Understanding Green Tourism', 'Travel differently. Experience places with care. Leave only good footprints.', '', NULL, 1),
(2, 'What Is Green Tourism?', 'Green tourism, also known as sustainable tourism or eco-responsible tourism, is a form of tourism that focuses on preserving the environment and promoting responsible practices to minimize the negative impact on nature. This type of tourism has been booming in recent years, with more and more travellers looking for authentic experiences in harmony with nature. Among the authentic and natural tourism practices, we can also mention slow tourism, linked to the \"slow life\" lifestyle, which reminds us to take the time to live our vacations in harmony with nature.', '', NULL, 2),
(3, 'Characteristics of green tourism', 'Green tourism is characterized by its respect for the environment and preservation of biodiversity. The main objective is to reduce the carbon footprint by avoiding activities that harm the environment or use too many resources. The aim is to preserve ecosystems, animal and plant species, and minimize the negative impact on nature. Green tourism favors responsible practices, such as the use of public transport, the consumption of local products, accommodation in eco-lodges and eco-friendly cottages, and support for local businesses that adhere to responsible practices.', '', NULL, 4),
(4, 'Green tourism activities', 'The activities on offer as part of green tourism are very diverse, but they all have one thing in common: respect for the environment and showcasing the natural and cultural riches of the regions visited. Hiking, biking, wildlife observation, visits to nature parks, organic farming and local gastronomy are all part of green tourism, and low-impact activities at that.', '', NULL, 5),
(5, 'Green Tourism in Action', '', 'https://s1.wklcdn.com/image_528/15856235/187170752/115897489.700x525.jpg', NULL, 6),
(6, 'The challenges of green tourism', 'Green tourism is not without its challenges. Limiting negative impacts on the environment and biodiversity is a major challenge, particularly in terms of waste management, water management and the protection of endangered species. In addition, it is important to make tourists aware of the importance of green tourism, so that they understand the impact of their travel choices on the environment and biodiversity in order to preserve the planet.', '', NULL, 7),
(7, 'Why Green Tourism Matters', 'Today, tourism is one of the world’s largest industries — but also one of the most demanding on natural resources. Green tourism offers a gentler alternative. It helps reduce waste, protect forests and rivers, promote cultural identity, and generate fair income for local people. Choosing green tourism means choosing a better future. It means discovering destinations without damaging them. It means leaving a positive impact long after the journey ends.', '', NULL, 8),
(8, 'Green Tourism in Tanjung Malim', 'Tanjung Malim is surrounded by lush landscapes, educational forests, and community farms. Its rivers, hills, and rural villages make it a natural hub for green tourism development. Notable locations include Bukit Perangin, a peaceful hill ideal for mindful hiking, and the Fig Farm, which offers eco-farming that connects visitors to local agriculture. Other attractions include Sungai Bil, a refreshing natural escape with clear waters, and UPSI Adventure Park, an outdoor education center that blends nature and learning. With growing interest from universities, communities, and local authorities, Tanjung Malim is becoming a model for educational green tourism in Malaysia.', '', NULL, 9),
(9, 'Travel Slowly, Travel Consciously', 'Green Tourism is more than a journey — it is a choice to be mindful, to protect, and to connect. When we travel with intention, every step becomes meaningful. Every destination becomes a home.', '', NULL, 10),
(10, 'What is Sustainable Tourism?', 'Sustainable tourism meets the needs of the present without compromising the ability of future generations. It focuses on the triple bottom line: People, Planet, and Profit.', 'https://img.youtube.com/vi/oL-X2iQi864/hqdefault.jpg', 'https://www.youtube.com/embed/oL-X2iQi864', 3);

-- --------------------------------------------------------

--
-- Table structure for table `messages_user`
--

CREATE TABLE `messages_user` (
  `message_id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `message` text COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `packages`
--

CREATE TABLE `packages` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `pricePerPax` decimal(10,2) NOT NULL,
  `type` varchar(50) NOT NULL COMMENT 'All-Inclusive or Customizable',
  `durationDays` int NOT NULL,
  `imageUrl` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `activitiesIds` json DEFAULT NULL COMMENT 'Stores IDs like [1, 2, 3]',
  `maxSlots` int NOT NULL DEFAULT '30',
  `availableDates` json DEFAULT NULL COMMENT 'Stores dates like ["2025-12-10", "2025-12-11"]',
  `startTime` time DEFAULT NULL,
  `endTime` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `packages`
--

INSERT INTO `packages` (`id`, `name`, `pricePerPax`, `type`, `durationDays`, `imageUrl`, `description`, `activitiesIds`, `maxSlots`, `availableDates`, `startTime`, `endTime`) VALUES
(104, 'River & Rainforest Adventure', '150.00', 'All-Inclusive', 2, 'uploads/air terjun lata perangin.jpg', '- Trekking at Bukit Perangin (explore the nature trails).\r\n\r\n- Picnic lunch by the cool river at Lata Perangin\r\n\r\n- Night walk to spot insects/frogs (educational angle).\r\n\r\n- Camping near the river.\r\n\r\n- All equipment Provided', '[]', 10, '[]', '00:00:00', '00:00:00'),
(105, 'Chinggung Heritage: Orang Asli Cultural Retreat', '180.00', 'All-Inclusive', 2, 'uploads/chinggung.jpg', '- Meet the Tok Batin (Village Head) and learn about the unique history of the Semai tribe.\r\n\r\n- Master the ancient art of hunting by learning how to use a traditional blowpipe (sumpit from village experts.\r\n\r\n- Take a guided trek to identify and taste native medicinal herbs and edible plants used for generations.\r\n\r\n- Enjoy a relaxing overnight stay in eco-friendly bamboo huts or chalets located right next to the cool river.\r\n\r\n- Cool off with a refreshing swim in the clean, natural waters of the Chinggung river.\r\n\r\n- Participate in a traditional weaving (Anyam) workshop using natural materials from the forest.\r\n\r\n- Package included traditional lunch and tutorial how to cook it by Orang Asli from Kampung Chinggung', '[]', 30, '[]', '00:00:00', '00:00:00'),
(106, 'Bernam River Fruit Feast: Glamping & Durian', '200.00', 'All-Inclusive', 2, 'uploads/teratak.jpg', '- Enjoy a unique stay at Bernam Nature Hut, sleeping in comfortable glamping tents right by the flowing river.\r\n\r\n- Indulge in an all-you-can-eat buffet of premium local fruits, including Durian (Kampung & Hybrid) and Rambutan from local farm.\r\n\r\n- Cool off with unlimited access to the river for swimming and relaxing in the crystal-clear water.\r\n\r\n- Savor a delicious BBQ dinner prepared fresh by the riverside under the night sky.\r\n\r\n- Wake up to the sounds of the jungle and fresh morning mist in a serene, eco-friendly environment.\r\n\r\n- Safe and shallow river spots perfect for families with children.', '[]', 30, '[]', '00:00:00', '00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `programs`
--

CREATE TABLE `programs` (
  `program_id` int NOT NULL,
  `program_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `imageUrl` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `duration_hours` int NOT NULL,
  `description` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `programs`
--

INSERT INTO `programs` (`program_id`, `program_name`, `imageUrl`, `price`, `start_time`, `end_time`, `duration_hours`, `description`) VALUES
(1, 'Research with Chatgpt', 'chatgpt.png', '50.00', '08:00:00', '12:00:00', 4, 'Learn how to use Chatgpt to doing research'),
(2, 'A time at Bil River', 'sungai_bil.jpg', '10.00', '17:00:00', '19:00:00', 2, 'spend your evening at Bil River'),
(3, 'Back to 1922 SITC', 'uploads/muziumupsi.jpeg', '5.00', '09:00:00', '11:00:00', 2, '- Visit the Muzium Pendidikan Nasional located within the green campus of Universiti Pendidikan Sultan Idris (UPSI)\r\n\r\n- Explore the history and development of education in Malaysia from early times to the modern era\r\n\r\n- View historical artifacts, archival documents, photographs, and interactive exhibitions related to national education\r\n\r\n- Experience educational tourism (edutourism) that combines learning, culture, and sustainability\r\n\r\n- Participate in guided museum tours that enhance understanding and appreciation of educational heritage\r\n\r\n- Suitable for students, educators, families, researchers, and cultural tourism enthusiasts'),
(4, 'Souvenir Workshop', 'uploads/souvenir.jpg', '50.00', '14:00:00', '18:00:00', 4, '- Discover traditional anyaman (woven handicrafts) made by local artisans using natural and sustainable materials\r\n\r\n- Learn about the cultural heritage and history of Malaysian weaving techniques passed down through generations\r\n\r\n- Souvenirs crafted from eco-friendly resources such as pandan leaves, mengkuang, or bamboo\r\n\r\n- Support local communities and small-scale artisans through responsible tourism purchases\r\n\r\n- Promote sustainable consumption by choosing handmade, reusable, and biodegradable souvenirs'),
(5, 'Night Walk at Museum', 'uploads/museumnight.jpg', '5.00', '21:00:00', '23:00:00', 2, '- Enjoy a guided night walk around Muzium Pendidikan Nasional in a calm and atmospheric evening setting\r\n\r\n- Experience the museum surroundings from a new perspective, combining heritage, learning, and nature\r\n\r\n- Learn unique stories, history, and educational milestones related to the museum and UPSI\r\n\r\n- Appreciate the natural environment and campus greenery while walking responsibly'),
(6, 'Wedding Gift & Sirih Junjung Workshop', 'uploads/sirih junjung.jpg', '20.00', '09:00:00', '12:00:00', 3, '- Participate in a hands-on workshop on traditional Malay wedding gifts and sirih junjung arrangement\r\n\r\n- Learn the cultural meaning and symbolism of sirih junjung in Malay wedding customs\r\n\r\n- Guided by local artisans or cultural practitioners with traditional knowledge\r\n\r\n- Use natural, reusable, and eco-friendly materials such as betel leaves, flowers, and traditional containers'),
(7, 'Rainforest Exploration', 'uploads/rainforest.webp', '20.00', '08:00:00', '12:00:00', 4, '- Explore the natural rainforest environment within UPSI Adventure Park\r\n\r\n- Experience a guided eco-adventure walk through forest trails surrounded by rich biodiversity\r\n\r\n- Learn about local flora and fauna, forest ecosystems, and environmental conservation\r\n\r\n'),
(8, 'Water Rafting at Ulu Slim', 'uploads/ulu slim.jpg', '180.00', '10:00:00', '14:00:00', 4, '- Eco-adventure activity conducted in a natural river environment\r\n\r\n- Located in Ulu Slim, surrounded by rainforest and rich biodiversity\r\n\r\n- Features gentle to moderate rapids, suitable for beginners and students\r\n\r\n- Provides an exciting way to experience nature and outdoor adventure'),
(9, 'A Tree A Day', 'uploads/plant a tree.jpg', '35.00', '08:30:00', '10:30:00', 2, '- Environmental activity focused on planting trees to increase oxygen supply\r\n\r\n- Encourages visitors to contribute directly to nature conservation\r\n\r\n- Helps reduce carbon dioxide and improve air quality\r\n\r\n- Supports climate action and environmental sustainability');

-- --------------------------------------------------------

--
-- Table structure for table `stats`
--

CREATE TABLE `stats` (
  `stats_id` int NOT NULL,
  `participants` int NOT NULL,
  `donation` decimal(10,2) NOT NULL,
  `vendors` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stats`
--

INSERT INTO `stats` (`stats_id`, `participants`, `donation`, `vendors`) VALUES
(1, 156, '7331.00', 67);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `created_at`, `updated_at`) VALUES
(1, 'zikriashraf', 'ttt@gmail.com', '$2y$10$0HD7p3ULf6fuSyIh4row1eFzqUJT2vl8f1aVmljSR9MtcS/8m6gMq', '2025-11-25 14:53:12', '2026-01-03 20:38:43'),
(3, 'www', 'www@gmail.com', '$2y$10$CSjSlYTaByVm8gAIsdNqleNKloQ1HljD9rc9uuTIYPdbFLDiRHx7G', '2025-12-21 06:48:35', '2025-12-21 06:48:35'),
(4, 'di', 'budiman@gmai.com', '$2y$10$yfe0ai.9jpVnPk9/1Ex/a.uUC.SWI7vClR/fRtLO4bvX2UwRX5x/q', '2026-01-04 09:41:44', '2026-01-04 09:41:44');

-- --------------------------------------------------------

--
-- Table structure for table `visitors_monthly`
--

CREATE TABLE `visitors_monthly` (
  `visitors_id` int NOT NULL,
  `month` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `year` int NOT NULL,
  `visitors_count` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `visitors_monthly`
--

INSERT INTO `visitors_monthly` (`visitors_id`, `month`, `year`, `visitors_count`) VALUES
(1, 'January', 2025, 1653),
(2, 'February', 2025, 518),
(3, 'March', 2025, 1293),
(4, 'April', 2025, 1063),
(5, 'May', 2025, 946),
(6, 'June', 2025, 315);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `analytics_lists`
--
ALTER TABLE `analytics_lists`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `analytics_stats`
--
ALTER TABLE `analytics_stats`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attractions`
--
ALTER TABLE `attractions`
  ADD PRIMARY KEY (`attractions_id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `package_id` (`package_id`);

--
-- Indexes for table `contact_details`
--
ALTER TABLE `contact_details`
  ADD PRIMARY KEY (`contact_id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `donations`
--
ALTER TABLE `donations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `explore_deeper`
--
ALTER TABLE `explore_deeper`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `explore_section`
--
ALTER TABLE `explore_section`
  ADD PRIMARY KEY (`explore_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`feedback_id`);

--
-- Indexes for table `gallery_images`
--
ALTER TABLE `gallery_images`
  ADD PRIMARY KEY (`image_id`);

--
-- Indexes for table `hero_slides`
--
ALTER TABLE `hero_slides`
  ADD PRIMARY KEY (`slide_id`);

--
-- Indexes for table `learn_content`
--
ALTER TABLE `learn_content`
  ADD PRIMARY KEY (`sections_id`);

--
-- Indexes for table `packages`
--
ALTER TABLE `packages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `programs`
--
ALTER TABLE `programs`
  ADD PRIMARY KEY (`program_id`);

--
-- Indexes for table `stats`
--
ALTER TABLE `stats`
  ADD PRIMARY KEY (`stats_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `visitors_monthly`
--
ALTER TABLE `visitors_monthly`
  ADD PRIMARY KEY (`visitors_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `analytics_lists`
--
ALTER TABLE `analytics_lists`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `analytics_stats`
--
ALTER TABLE `analytics_stats`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `attractions`
--
ALTER TABLE `attractions`
  MODIFY `attractions_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `contact_details`
--
ALTER TABLE `contact_details`
  MODIFY `contact_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `donations`
--
ALTER TABLE `donations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `explore_deeper`
--
ALTER TABLE `explore_deeper`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `explore_section`
--
ALTER TABLE `explore_section`
  MODIFY `explore_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `feedback_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `gallery_images`
--
ALTER TABLE `gallery_images`
  MODIFY `image_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `hero_slides`
--
ALTER TABLE `hero_slides`
  MODIFY `slide_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `learn_content`
--
ALTER TABLE `learn_content`
  MODIFY `sections_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `packages`
--
ALTER TABLE `packages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;

--
-- AUTO_INCREMENT for table `programs`
--
ALTER TABLE `programs`
  MODIFY `program_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `stats`
--
ALTER TABLE `stats`
  MODIFY `stats_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `visitors_monthly`
--
ALTER TABLE `visitors_monthly`
  MODIFY `visitors_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `fk_package_booking` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
