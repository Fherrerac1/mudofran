import axios from 'axios';
import authHeader from './auth-header';
import Jsona from 'jsona';


const dataFormatter = new Jsona();

export default {
  async getProfile() {
    const response = await axios.get("/me", { headers: authHeader() })
    return dataFormatter.deserialize(response.data);
  },

  async editProfile(profile) {
    profile.type = 'profile'
    const newJson = dataFormatter.serialize({ stuff: profile })
    const response = await axios.patch("/me", newJson, { headers: authHeader() })
    return dataFormatter.deserialize(response.data);
  },

  async uploadPic(pic, userId) {
    const postUrl = "/uploads/users/" + userId + "/profile-image";
    const response = await axios.post(postUrl,
      { attachment: pic },
      { headers: { 'Content-Type': 'multipart/form-data' } }
    );
    return response.data;
  }

}