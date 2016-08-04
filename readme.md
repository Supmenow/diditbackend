# Did It
The API for the Did it app


# Create a user
1. POST to http://139.59.189.137/api/v1/users (IP will change to URI soon)
2. POST raw data as {"name":"Legend","phone":"00000000"}. 
3. Provide header of "api-secret" = 75bf2f1b372ce11b1b082b6a5b64c504be56e00fa4cfd5c8cae29fa540a4c2ec
4. You will receive a 200 response with the user data.
