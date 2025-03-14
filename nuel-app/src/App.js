import React, { useState } from 'react';
import Register from './Register';
import Login from './Login';

const App = () => {
  const [isRegistered, setIsRegistered] = useState(false);

  return (
    <div>
      <h1>{isRegistered ? 'Login' : 'Register'}</h1>
      {isRegistered ? (
        <Login />
      ) : (
        <Register />
      )}
      <button onClick={() => setIsRegistered(!isRegistered)}>
        {isRegistered ? 'Need to Register?' : 'Already Registered?'}
      </button>
    </div>
  );
};

export default App;
