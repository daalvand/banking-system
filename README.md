## Banking System

The Banking System is a Dockerized application designed for managing fund transfers.

### Installation and Setup

1. **Clone the repository:**

   ```bash
   git clone https://github.com/daalvand/banking-system.git
   cd banking-system
   ```

2. **Create a Configuration File:**

   Create a `.env` file by duplicating the provided `.env.example` file (`cp .env.example .env`). Customize the environment variables as needed.

3. **Start the Docker Containers:**

   ```bash
   docker-compose up -d
   ```

4. **Install Application Dependencies:**

   ```bash
   docker-compose exec app composer install
   ```

5. **Generate Application Key:**

   ```bash
   docker-compose exec app php artisan key:generate
   ```

6. **Migrate the Database and Seed Initial Data:**

   ```bash
   docker-compose exec app php artisan migrate --seed
   ```

7. **Access the Application:**

   Visit [http://127.0.0.1:8000](http://127.0.0.1:8000) in your web browser to access the Banking System application.

### API Routes

- **GET `/api/v1/transactions/top-users`**

  Retrieve top users based on transaction history.

- **POST `/api/v1/transactions/transfer`**

  Perform a fund transfer between accounts.

  **Request Body:**
  ```json
  {
    "source_card": 1111222233334444,
    "destination_card": 6209444444443333,
    "amount": 10000
  }
  ```

### Running Tests

To run the application tests, first create `database/database.sqlite`:

```bash
touch database/database.sqlite
```

Then execute the following command:

```bash
docker-compose exec app php artisan test
```

### Continuous Integration (CI)

The GitHub workflow includes:
- Building and pushing Docker images to Docker Hub.
- Executing tests for the application.
