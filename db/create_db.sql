-- Users Table
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password TEXT NOT NULL,
    role VARCHAR(20) DEFAULT 'user', -- 'admin', 'moderator', 'user'
    total_likes INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT NOW()
);


-- Group table
CREATE TABLE groups (
    id SERIAL PRIMARY KEY,
    name TEXT NOT NULL,
    description TEXT,
    user_id INT REFERENCES users(id) ON DELETE CASCADE
);


-- Questions Table
CREATE TABLE questions (
    q_id SERIAL PRIMARY KEY,
    question TEXT NOT NULL,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    group_id INT REFERENCES groups(id) ON DELETE CASCADE,
    likes INT DEFAULT 0,
    dislikes INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT NOW()
);

-- Answers Table
CREATE TABLE answers (
    a_id SERIAL PRIMARY KEY,
    q_id INT REFERENCES questions(q_id) ON DELETE CASCADE,
    answer TEXT NOT NULL,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    likes INT DEFAULT 0,
    dislikes INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT NOW()
);

-- Friend Requests Table
CREATE TABLE friend_requests (
    request_id SERIAL PRIMARY KEY,
    sender_id INT REFERENCES users(id) ON DELETE CASCADE,
    receiver_id INT REFERENCES users(id) ON DELETE CASCADE,
    status VARCHAR(10) DEFAULT 'pending', -- 'pending', 'accepted', 'rejected'
    created_at TIMESTAMP DEFAULT NOW()
);

-- Messages Table
CREATE TABLE messages (
    message_id SERIAL PRIMARY KEY,
    sender_id INT REFERENCES users(id) ON DELETE CASCADE,
    receiver_id INT REFERENCES users(id) ON DELETE CASCADE,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT NOW()
);
