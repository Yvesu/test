import React, { Component } from 'react'
import StepsFragment from './steps/StepsFragment'
import {Layout} from 'antd'

//片段 - 上传片段
class FragmentUpload extends Component{

  render(){
    // console.log(this);
    return(
      <Layout className="fragment_box">
        <h3>发布片段</h3>
        <StepsFragment />
      </Layout>
    )
  }
}

export default FragmentUpload
