import React, { useState } from 'react';
import axios from 'axios';

const Register = () => {
  const [userData, setUserData] = useState({
    first_name: '',
    middle_name: '',
    last_name: '',
    phone_number: '',
    username: '',
    password: '',
    group_name: ''
  });

  const handleChange = (e) => {
    const { name, value } = e.target;
    setUserData({ ...userData, [name]: value });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      const response = await axios.post('http://localhost/Nuel/register.php', userData);
      alert(response.data.message);
    } catch (error) {
      console.error(error);
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      <input 
        type="text" 
        name="first_name" 
        placeholder="First Name" 
        onChange={handleChange} 
        required 
      />
      <input 
        type="text" 
        name="middle_name" 
        placeholder="Middle Name" 
        onChange={handleChange} 
      />
      <input 
        type="text" 
        name="last_name" 
        placeholder="Last Name" 
        onChange={handleChange} 
        required 
      />
      <input 
        type="text" 
        name="phone_number" 
        placeholder="Phone Number" 
        onChange={handleChange} 
        required 
      />
      <input 
        type="text" 
        name="username" 
        placeholder="Username" 
        onChange={handleChange} 
        required 
      />
      <input 
        type="password" 
        name="password" 
        placeholder="Password" 
        onChange={handleChange} 
        required 
      />
      <input 
        type="text" 
        name="group_name" 
        placeholder="Group Name" 
        onChange={handleChange} 
        required 
      />
      <button type="submit">Register</button>
    </form>
  );
};

export default Register;
