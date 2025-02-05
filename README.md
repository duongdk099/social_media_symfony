# Social Media Symfony

Social Media Symfony is a social media platform inspired by Reddit, built using the Symfony framework. The project allows users to create and manage communities (Subworlds), share posts, comment, and interact with other users through voting, private messaging, and more.

---

## 📜 Project Constraints and Features

This project was developed as part of a Symfony challenge and adheres to the following constraints:

### **Core Requirements**
- **Entities**: At least 10 entities with inheritance.
- **Relations**: At least 2 ManyToMany and 8 OneToMany relations.
- **Security**: 
  - Secure user authentication.
  - At least 1 custom voter.
  - 3 different roles for access control.
- **API**:
  - A dedicated API controller.
  - Email sending functionality.
  - Integration with an external API (to be defined).
- **Testing**:
  - At least 1 unit test and 1 functional test.
  - Custom queries using QueryBuilder in repositories.
  - Dynamic forms.
  - Admin panel.
  - At least 10 distinct pages.
- **CI/CD**: Deployment-ready with continuous integration and static analysis tools.

### **Bonus Features**
- Real-time updates (planned for future implementation with Mercure).
- Asynchronous operations.
- Custom commands.
- Mutation testing.
- Domain-Driven Design (DDD) and Test-Driven Development (TDD).

---

## 🚀 Features

- **User Management**: Secure authentication, multiple roles (Admin, Moderator, User).
- **Community Management**: Subworlds (similar to subreddits) for thematic discussions.
- **Content Sharing**: Create posts with media attachments, comment on posts, vote on content.
- **Private Messaging**: Users can exchange private messages.
- **Notifications**: System to notify users about relevant activities.
- **Content Reporting**: Report inappropriate content for moderation.
- **Data Persistence**: Includes sample data (fixtures) for testing.

---

## 🛠️ Requirements

- **PHP 8.2+**
- **Composer**
- **Docker & Docker Compose**
- **MySQL** (via Docker)

---

## ⚙️ Installation and Setup

### **1. Clone the Repository**
```bash
git clone https://github.com/duongdk099/social_media_symfony.git
cd social_media_symfony
```

### **2. Configure Environment Variables**
Copy the `.env` file to `.env.local`:
```bash
cp .env .env.local
```
Update the following variables in `.env.local`:
```env
DATABASE_URL="mysql://app:securepassword@database:3306/subworld?serverVersion=8.0"
```

### **3. Start Docker Containers**
Ensure Docker is running and start the services:
```bash
docker-compose up -d --build
```

---

## 🛠️ Symfony Configuration

### **4. Install Dependencies**
Run Composer inside the Docker container:
```bash
docker exec -it symfony_app composer install
```

### **5. Create the Database**
Run the following commands to set up the database schema:
```bash
docker exec -it symfony_app php bin/console doctrine:database:create
docker exec -it symfony_app php bin/console doctrine:migrations:migrate
```

### **6. Load Fixtures**
Load sample data for testing:
```bash
docker exec -it symfony_app php bin/console doctrine:fixtures:load
```

---

## 🏃‍♂️ Running the Application

Access the application in your browser at [http://localhost:8000](http://localhost:8000).

---

## 🧪 Testing

To execute tests, run:
```bash
docker exec -it symfony_app php bin/phpunit
```

---

## 📂 Project Structure

- **src/Entity**: Main entities (User, Post, Comment, etc.).
- **src/Repository**: Custom repository queries.
- **src/DataFixtures**: Fixtures for populating the database.
- **public/**: Entry point for the application.
- **migrations/**: Doctrine migrations for database schema.

---

## 🌟 Future Improvements

- **Real-Time Features**: Implement real-time updates using Mercure.
- **Notifications**: Push notifications for user activity.
- **CI/CD Integration**: Set up automated pipelines for deployment and testing.

---

## 🤝 Contributions

Contributions are welcome! Feel free to open an issue or submit a pull request.

---

## 📝 License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.
