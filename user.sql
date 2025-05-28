CREATE TABLE player (
    player_id CHAR(8) PRIMARY KEY NOT NULL,
    player_name VARCHAR(20) NOT NULL,
    player_password VARCHAR(30) NOT NULL,
    gacha_stone INT NOT NULL DEFAULT 0,
    player_money INT NOT NULL DEFAULT 0
);

CREATE TABLE role (
    role_id INT PRIMARY KEY AUTO_INCREMENT,
    role_name VARCHAR(30) NOT NULL
);

CREATE TABLE player_role (
    player_id CHAR(8) NOT NULL,
    role_id INT NOT NULL,
    owned BOOLEAN DEFAULT TRUE,
    PRIMARY KEY (player_id, role_id),
    FOREIGN KEY (player_id) REFERENCES player(player_id),
    FOREIGN KEY (role_id) REFERENCES role(role_id)
);

CREATE TABLE tool (
    tool_id INT PRIMARY KEY AUTO_INCREMENT,
    tool_name VARCHAR(30) NOT NULL
);

CREATE TABLE player_tool (
    player_id CHAR(8) NOT NULL,
    tool_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    PRIMARY KEY (player_id, tool_id),
    FOREIGN KEY (player_id) REFERENCES player(player_id),
    FOREIGN KEY (tool_id) REFERENCES tool(tool_id)
);

-- 預設角色
INSERT INTO role (role_name) VALUES
('火焰騎士'), ('冰霜魔導'), ('雷光刺客'), ('森林祭司'),
('黑暗騎士'), ('光明戰士'), ('風之使者'), ('地獄獸王');

-- 預設道具
INSERT INTO tool (tool_name) VALUES
('回復藥水'), ('經驗書'), ('進化石'), ('強化石'),
('金幣箱'), ('抽卡券'), ('神秘寶箱');

--players
INSERT INTO player (player_id, player_name, player_password, gacha_stone, player_money)
VALUES ('00000001', 'TestPlayer', 'password1234', 100, 1000);
