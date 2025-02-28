# MP9 API Endpoints Documentation

## Basic Authentication Endpoints

### Create New User
```
POST /api/v1/auth/signup
```
**Request Body:**
```json
{
  "firstName": "string",
  "lastName": "string",
  "username": "string",
  "password": "string",
  "confirmPassword": "string",
  "age": "int"
}
```

### Login User
```
POST /api/v1/auth/login
```
**Request Body:**
```json
{
  "username": "string",
  "password": "string"
}
```

### Logout User
```
POST /api/v1/auth/logout
```

### User Forgot Password
```
POST /api/v1/auth/forgot-password
```

## Dashboard

### Storage Metrics
```
GET /api/v1/metrics/storage
```
**Response:**
```json
{
  "totalStorageUsed": "number",
  "databases": [
    {
      "name": "string",
      "usagePercentage": "number"
    }
  ]
}
```

### Performance Metrics
```
GET /api/v1/metrics/performance
```
**Response:**
```json
{
  "databases": [
    {
      "name": "string",
      "metrics": {
        "monthly": [],
        "users": [],
        "assets": []
      }
    }
  ],
  "errors": {
    "queryErrors": []
  }
}
```

## User Settings

### User Profile Endpoints

#### Get User Profile
```
GET /api/v1/users/profile
```
**Response:**
```json
{
  "firstName": "string",
  "lastName": "string",
  "username": "string",
  "email": "string",
  "gender": "string",
  "profileImage": "string"
}
```

#### Update User Profile
```
PUT /api/v1/users/profile
```
**Request Body:**
```json
{
  "firstName": "string",
  "lastName": "string",
  "gender": "string",
  "email": "string"
}
```

### Account Management
```
POST /api/v1/users/deactivate
POST /api/v1/users/delete
```

### Appearance/Theme Settings

#### Get User Preferences
```
GET /api/v1/users/preferences
```
**Response:**
```json
{
  "Theme": "light" | "dark"
}
```

#### Update User Preferences
```
PUT /api/v1/users/preferences
```
**Request Body:**
```json
{
  "theme": "light" | "dark"
}
```

## Query Interface

### List Databases and Collections

#### Get All Databases
```
GET /api/v1/databases
```
**Response:**
```json
{
  "Databases": []
}
```

#### Get Collections for a Specific Database
```
GET /api/v1/databases/:name/collections
```
**Response:**
```json
{
  "Collections": []
}
```

### Query Execution Endpoints

#### Execute a Query
```
POST /api/v1/query
```
**Request Body:**
```json
{
  "database": "Database 1",
  "collection": "Collection 1",
  "query": {
    "status": "active"
  }
}
```
**Response:**
```json
{
  "results": [],
  "count": "number",
  "executionTime": "number"
}
```

## Data: To Explore or View the Data

### Get All Documents in a Collection
```
GET /api/v1/databases/:database/collections/:collection/documents
```
**Query Parameters:**
- `page`: number
- `limit`: number
- `sort`: string
- `order`: string
- `filter`: string

### Get a Single Document
```
GET /api/v1/databases/:database/collections/:collection/documents/:id
```

### Create a New Document
```
POST /api/v1/databases/:database/collections/:collection/documents
```
**Request Body:**
```json
{
  "st_num": "string",
  "st_name": "string",
  "st_surname": "string",
  "degree": "string"
}
```

### Update a Document
```
PUT /api/v1/databases/:database/collections/:collection/documents/:id
```
**Request Body:**
```json
{
  "st_name": "string",
  "degree": "string"
}
```

### Delete a Document
```
DELETE /api/v1/databases/:database/collections/:collection/documents/:id
```

### Batch Operations
```
POST /api/v1/databases/:database/collections/:collection/documents/bulk
```
**Request Body:**
```json
{
  "action": "delete" | "update",
  "ids": ["u25039217", "u24113462"],
  "update": {
    "degree": "BSc in Computer Science"
  }
}
```

### Create a Collection
```
POST /api/v1/databases/:database/collections
```
**Request Body:**
```json
{
  "name": "string"
}
```

### Delete a Collection
```
DELETE /api/v1/databases/:database/collections/:name
```

### Real-time Functionality
```
WS /api/v1/changes
```

## Admin Dashboard

### List All Users
```
GET /api/v1/admin/users
```
**Response:**
```json
{
  "users": []
}
```

### List All Databases
```
GET /api/v1/admin/databases
```
**Response:**
```json
{
  "databases": []
}
```