CREATE TABLE `afdelinger` (
  `afdelingID` int(11) NOT NULL AUTO_INCREMENT,
  `afdeling` varchar(255) NOT NULL,
  `order_number` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`afdelingID`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `afdelinger_ems` (
  `afdelingID` int(11) NOT NULL AUTO_INCREMENT,
  `afdeling` varchar(255) NOT NULL,
  `order_number` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`afdelingID`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `dailyreport` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dato` varchar(255) NOT NULL DEFAULT current_timestamp(),
  `username` varchar(255) NOT NULL,
  `kommentar` longtext NOT NULL,
  `titel` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `updated_by` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=143 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `gangs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gang_name` varchar(255) NOT NULL,
  `order_number` int(11) NOT NULL DEFAULT 0,
  `created_by` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `licenses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `license_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

CREATE TABLE `licenses_subjects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `license_emne` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `order_number` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

CREATE TABLE `population` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `dob` varchar(255) NOT NULL,
  `height` int(11) NOT NULL,
  `sex` varchar(255) NOT NULL,
  `phone_number` int(11) NOT NULL,
  `gang` varchar(255) DEFAULT NULL,
  `note` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1506 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `population_cases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dato` varchar(255) NOT NULL DEFAULT current_timestamp(),
  `pid` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `sigtet` longtext NOT NULL,
  `ticket` int(11) NOT NULL,
  `prison` int(11) NOT NULL,
  `klip` int(11) NOT NULL,
  `status` varchar(255) NOT NULL,
  `comment` longtext NOT NULL,
  `cases` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`cases`)),
  `erkender` tinyint(4) DEFAULT 0,
  `conditional` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12255 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `population_ems` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `steamid` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `dob` varchar(255) NOT NULL,
  `height` int(11) NOT NULL,
  `sex` varchar(255) NOT NULL,
  `phone_number` int(11) NOT NULL,
  `note` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=410 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `population_journals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dato` varchar(2555) NOT NULL DEFAULT current_timestamp(),
  `pid` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `arrival` varchar(255) NOT NULL,
  `damage_report` longtext NOT NULL,
  `treatment_before_arrival` longtext NOT NULL,
  `condition_at_arrival_resp` longtext NOT NULL,
  `condition_at_arrival_cirk` longtext NOT NULL,
  `condition_at_arrival_bleed` longtext NOT NULL,
  `condition_at_arrival_pain` longtext NOT NULL,
  `damage_assessment` longtext NOT NULL,
  `follow_up_treatment` longtext NOT NULL,
  `recept_given` varchar(255) NOT NULL,
  `medicin_given` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=519 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `population_psykjournals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dato` varchar(255) NOT NULL DEFAULT current_timestamp(),
  `pid` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `reason` longtext NOT NULL,
  `epikrise` longtext NOT NULL,
  `conversation` longtext NOT NULL,
  `medicin_treatment` longtext NOT NULL,
  `psykolog_assessment` longtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `population_vehicles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dato` date NOT NULL DEFAULT current_timestamp(),
  `username` varchar(255) NOT NULL,
  `plate` varchar(255) NOT NULL,
  `reason` longtext NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `owner` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=228 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `population_wanted` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dato` varchar(255) NOT NULL DEFAULT current_timestamp(),
  `username` varchar(255) NOT NULL,
  `target_id` int(11) NOT NULL,
  `sigtet` longtext NOT NULL,
  `reason` longtext NOT NULL,
  `ticket` int(11) NOT NULL,
  `prison` int(11) NOT NULL,
  `frakendelse` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `klip` int(11) DEFAULT 0,
  `updated_by` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=638 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `punishment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticketemne` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `order_number` int(11) NOT NULL,
  `hasPrison` tinyint(1) NOT NULL DEFAULT 0,
  `hasVehicle` tinyint(1) NOT NULL DEFAULT 0,
  `hasStoffer` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

CREATE TABLE `tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `emne` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `paragraf` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `sigtelse` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `ticket` int(11) NOT NULL,
  `klip` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `frakendelse` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `information` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `prison` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=239 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `firstname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `lastname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `job` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `role` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `afdeling` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `licenses` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `WebsiteAdmin` tinyint(1) NOT NULL,
  `phone_number` varchar(255) DEFAULT NULL,
  `only_admin` tinyint(1) NOT NULL DEFAULT 0,
  `isOnDuty` int(11) NOT NULL DEFAULT 0,
  `steamid` varchar(255) DEFAULT NULL,
  `patrol_id` varchar(255) DEFAULT NULL,
  `patrol_category` varchar(255) DEFAULT NULL,
  `patrol_task` longtext NOT NULL DEFAULT '',
  `patrol_user_override` int(11) NOT NULL DEFAULT 0,
  `hasGangAccess` tinyint(1) NOT NULL DEFAULT 0,
  `nickname` varchar(255) NOT NULL DEFAULT '',
  `department` varchar(255) NOT NULL DEFAULT 'none',
  `hasPdfPrivilege` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `username` (`username`),
  KEY `username_2` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=653 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

CREATE TABLE `users_ems` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` date NOT NULL DEFAULT current_timestamp(),
  `firstname` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `job` varchar(255) NOT NULL,
  `WebsiteAdmin` tinyint(1) NOT NULL DEFAULT 0,
  `role` varchar(255) NOT NULL,
  `afdeling` varchar(255) NOT NULL,
  `hasPsykologAccess` tinyint(1) NOT NULL DEFAULT 0,
  `only_admin` tinyint(1) NOT NULL DEFAULT 0,
  `isOnDuty` tinyint(1) NOT NULL DEFAULT 0,
  `steamid` varchar(255) DEFAULT NULL,
  `patrol_id` varchar(255) DEFAULT NULL,
  `patrol_category` varchar(255) DEFAULT NULL,
  `patrol_task` longtext NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `username_ems` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=90 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `wanted` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dato` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `target_id` int(11) DEFAULT NULL,
  `target` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `sigtet` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `reason` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `ticket` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `prison` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `frakendelse` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3387 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

CREATE TABLE `wanted_vehicles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dato` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `plate` varchar(255) NOT NULL,
  `reason` mediumtext NOT NULL,
  `status` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=632 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

INSERT INTO `users` (
    `username`, `password`, `firstname`, `lastname`, `job`, `role`, `afdeling`, 
    `licenses`, `WebsiteAdmin`, `phone_number`, `only_admin`, `isOnDuty`, `steamid`, 
    `patrol_id`, `patrol_category`, `patrol_task`, `patrol_user_override`, `hasGangAccess`, 
    `nickname`, `department`, `hasPdfPrivilege`
) VALUES (
    '00', '$2b$12$F68Hb9FLmQ1PZjxgMc6P2etoggne3BtDDMU20v.jDJXWW0kVQn6dS', 'John', 'Doe', 'Worker', 'Admin', 'General', 
    '', 1, NULL, 0, 0, NULL, 
    NULL, NULL, '', 0, 0, 
    '', 'none', 0
);
