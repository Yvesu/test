import React, { Component } from 'react'
import { Button, Menu, Dropdown, Icon} from 'antd'

import ModalTransfer from '../../modal/ModalTransfer'
import ModalTop from '../../modal/ModalTop'
import ModalScreen from '../../modal/ModalScreen'
import ModalUpAndDown from '../../modal/ModalUpAndDown'
import ModalActive from '../../modal/ModalActive'
import ModalPromotion from '../../modal/ModalPromotion'
import ModalFrozen from '../../modal/ModalFrozen'
import ModalThaw from '../../modal/ModalThaw'
import ModalDelete from '../../modal/ModalDelete'

// import Fetch from 'utils/fetch'

//下拉菜单 - 批量操作
class BatchCommonMenu extends Component {
  constructor(props){
    super(props)
     this.state={
       visible:false,
     }
  }

  render() {

    const tableID = this.props.SelectAll
    return (
      <Dropdown overlay={
        <Menu className="dropMenu" style={{textAlign:"center"}}>
          {
            (this.props.menuName &&this.props.menuName.dotype)? (
              <Menu.Item  key="1">
                <ModalTransfer id={tableID}
                  visible={this.state.visible}
                  btnName={this.props.menuName.dotype}
                  uri={this.props.ModalPopUri}
                  RefreshTableState={this.props.RefreshTableState}
                />
              </Menu.Item>
            ) :null
          }

          {
            (this.props.menuName &&this.props.menuName.recomment)? (
              <Menu.Item  key="2">
                <Button>推荐</Button>
              </Menu.Item>
            ) :null
          }

          {
          (this.props.menuName &&this.props.menuName.ishot)? (
              <Menu.Item  key="3">
                  <Button>热门</Button>
              </Menu.Item>
            ) :null
          }
        {
        (this.props.menuName &&this.props.menuName.recommend)? (
            <Menu.Item  key="4">
              <ModalTop id={tableID} visible={this.state.visible}
                uri={this.props.ModalTopUri}
                btnName={this.props.menuName.recommend}
                RefreshTableState={this.props.RefreshTableState}
              />
              </Menu.Item>
            ) :null
          }
          {
            ((this.props.menuName &&this.props.menuName.isheild) ||
              (this.props.menuName && this.props.menuName.isshield))? (
              <Menu.Item  key="5">
                <ModalScreen id={tableID} visible={this.state.visible}
                  btnName={this.props.menuName.isheid}
                  uri={this.props.ModalScreenUri}
                  RefreshTableState={this.props.RefreshTableState}
                />
              </Menu.Item>
            ) :null
          }

          {
            (this.props.menuName &&this.props.menuName.up)? (
              <Menu.Item  key="6">
                  <ModalUpAndDown
                    btnName={this.props.menuName.up}
                    uri={this.props.upUri}
                    id={tableID}
                    RefreshTableState={this.props.RefreshTableState}
                  />
              </Menu.Item>
            ) :null
          }
          {
            (this.props.menuName &&this.props.menuName.down)? (
              <Menu.Item  key="7">
                <ModalUpAndDown
                  btnName={this.props.menuName.down}
                  uri={this.props.downUri}
                  id={tableID}
                  RefreshTableState={this.props.RefreshTableState}
                />
              </Menu.Item>
            ) :null
          }
          {
            (this.props.menuName &&this.props.menuName.dc)? (
              <Menu.Item  key="8">
                <ModalActive
                  id={tableID}
                  btnName={this.props.menuName.dc}
                  activeUri={this.props.activeUri? this.props.activeUri : ''}
                  // cancelUri={this.props.cancelUri? this.props.cancelUri : ''}
                  // disableUri={this.props.disableUri? this.props.disableUri : ''}
                  RefreshTableState={this.props.RefreshTableState}
                />
              </Menu.Item>
            ) :null
          }
          {
            (this.props.menuName &&this.props.menuName.cc)? (
              <Menu.Item  key="9">
                <ModalActive
                  id={tableID}
                  btnName={this.props.menuName.cc}
                  // activeUri={this.props.activeUri? this.props.activeUri : ''}
                  cancelUri={this.props.cancelUri? this.props.cancelUri : ''}
                  // disableUri={this.props.disableUri? this.props.disableUri : ''}
                  RefreshTableState={this.props.RefreshTableState}
                />
              </Menu.Item>
            ) :null
          }
          {
            (this.props.menuName &&this.props.menuName.levelUp)? (
              <Menu.Item  key="10">
                <ModalPromotion
                  id={tableID}
                  btnName={this.props.menuName.levelUp}
                  goupUri={this.props.goupUri? this.props.goupUri : ''}
                  RefreshTableState={this.props.RefreshTableState}
                />
              </Menu.Item>
            ) :null
          }
          {
            (this.props.menuName &&this.props.menuName.stop)? (
              <Menu.Item  key="11">
                <ModalFrozen
                  id={tableID}
                  btnName={this.props.menuName.stop}
                  frozenUri={this.props.frozenUri? this.props.frozenUri : ''}
                  RefreshTableState={this.props.RefreshTableState}
                />
              </Menu.Item>
            ) :null
          }
          {
            (this.props.menuName &&this.props.menuName.stop)? (
              <Menu.Item  key="11">
                <ModalFrozen
                  id={tableID}
                  btnName={this.props.menuName.stop}
                  frozenUri={this.props.frozenUri? this.props.frozenUri : ''}
                  RefreshTableState={this.props.RefreshTableState}
                />
              </Menu.Item>
            ) :null
          }
          {
            (this.props.menuName &&this.props.menuName.cs)? (
              <Menu.Item  key="12">
                <ModalThaw
                  id={tableID}
                  btnName={this.props.menuName.cs}
                  thawUri={this.props.thawUri? this.props.thawUri : ''}
                  RefreshTableState={this.props.RefreshTableState}
                />
              </Menu.Item>
            ) :null
          }
          {
            (this.props.menuName &&this.props.menuName.delete)? (
              <Menu.Item  key="13">
                <ModalDelete
                  id={tableID}
                  btnName={this.props.menuName.delete}
                  deleteUri={this.props.deleteUri? this.props.deleteUri : ''}
                  RefreshTableState={this.props.RefreshTableState}
                />
              </Menu.Item>
            ) :null
          }
          {/* {
            this.state.menuItem.ishot? (
              <Menu.Item  key="8">
                  <Button>启用</Button>
              </Menu.Item>
            ) :null
          }
          {
            this.state.menuItem.ishot? (
              <Menu.Item  key="9">
                  <Button>删除</Button>
              </Menu.Item>
            ) :null
          } */}


        </Menu>}
        trigger={['click']}>
        <Button type="primary">
           {"批量操作"}
              <Icon type="down" />
        </Button>
      </Dropdown>
      )
  }


}

export default BatchCommonMenu
