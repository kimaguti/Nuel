import React, { useState } from 'react';
import './App.css';  // Import the CSS file

import Register from './Register';
import Login from './Login';

const App = () => {
  const [isRegistered, setIsRegistered] = useState(false);

  return (
    <div className="form-container">
      <h1>{isRegistered ? 'Login' : 'Register'}</h1>
      {isRegistered ? (
        <Login />
      ) : (
        <Register />
      )}
      <div className="toggle-button">
        <button onClick={() => setIsRegistered(!isRegistered)}>
          {isRegistered ? 'Need to Register?' : 'Already Registered?'}
        </button>
      </div>
    </div>
  );
};

export default App;
